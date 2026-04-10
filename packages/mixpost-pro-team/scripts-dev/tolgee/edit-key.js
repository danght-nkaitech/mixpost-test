#!/usr/bin/env node

//
// Edit an existing translation key in Tolgee (name, translations, tags).
//
// Usage:
//   npm run edit-key -- <keyName> ["locale=value" ...] [--name=newName] [--tag=tag1,tag2]
//
// Examples:
//   npm run edit-key -- service.mastodon.connect "en-GB=Connect to Mastodon" "fr-FR=Se connecter"
//   npm run edit-key -- service.mastodon.connect --name=service.mastodon.link
//   npm run edit-key -- service.mastodon.connect --tag=draft:my-branch,production
//   npm run edit-key -- service.mastodon.connect "en-GB=Updated text" --tag=draft:my-branch
//
// Tags are only modified when --tag is explicitly provided; otherwise left unchanged.
// Laravel :variable syntax is converted to Tolgee {variable} format.
//

import { readFileSync, existsSync } from 'node:fs'
import { resolve, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = dirname(fileURLToPath(import.meta.url))
const ROOT = resolve(__dirname, '../..')

// ─── Logging ──────────────────────────────────────────────

function log(msg) {
  console.log(`[edit-key] ${msg}`)
}

function logError(msg) {
  console.error(`[edit-key] ERROR: ${msg}`)
}

// ─── Environment ──────────────────────────────────────────

function loadEnv() {
  const envPath = resolve(ROOT, '.env')

  if (!existsSync(envPath)) {
    logError('.env file not found at project root.')
    process.exit(1)
  }

  const env = {}

  for (const line of readFileSync(envPath, 'utf-8').split('\n')) {
    const trimmed = line.trim()
    if (!trimmed || trimmed.startsWith('#')) continue

    const eqIndex = trimmed.indexOf('=')
    if (eqIndex === -1) continue

    env[trimmed.slice(0, eqIndex).trim()] = trimmed.slice(eqIndex + 1).trim()
  }

  return env
}

function getConfig(env) {
  const required = ['TOLGEE_API_URL', 'TOLGEE_API_KEY', 'TOLGEE_PROJECT_ID']
  const missing = required.filter(key => !env[key])

  if (missing.length) {
    logError(`Missing required .env variables: ${missing.join(', ')}`)
    process.exit(1)
  }

  return {
    apiUrl: env.TOLGEE_API_URL,
    apiKey: env.TOLGEE_API_KEY,
    apiVersion: env.TOLGEE_API_VERSION || 'v2',
    projectId: env.TOLGEE_PROJECT_ID
  }
}

// ─── CLI Arguments ────────────────────────────────────────

function parseArgs() {
  const args = process.argv.slice(2)
  let keyName = null
  let newName = null
  let tags = null
  const translations = {}

  for (const arg of args) {
    if (arg.startsWith('--name=')) {
      newName = arg.slice('--name='.length)
    } else if (arg.startsWith('--tag=')) {
      tags = arg.slice('--tag='.length).split(',').filter(Boolean)
    } else if (arg.includes('=') && !arg.startsWith('--')) {
      const eqIndex = arg.indexOf('=')
      const locale = arg.slice(0, eqIndex)
      const value = arg.slice(eqIndex + 1)
      translations[locale] = value
    } else if (!keyName && !arg.startsWith('--')) {
      keyName = arg
    }
  }

  if (!keyName) {
    logError('Missing key name.')
    console.error('')
    console.error(
      'Usage: npm run edit-key -- <keyName> ["locale=value" ...] [--name=newName] [--tag=tag1,tag2]'
    )
    console.error('Example: npm run edit-key -- service.mastodon.connect "en-GB=Updated text"')
    process.exit(1)
  }

  const hasUpdates = Object.keys(translations).length || newName || tags !== null
  if (!hasUpdates) {
    logError('Nothing to update. Provide translations, --name, or --tag.')
    process.exit(1)
  }

  return { keyName, newName, translations, tags }
}

// ─── Translation Formatting ──────────────────────────────

function laravelToTolgee(text) {
  return text.replace(/:(\w+)/g, '{$1}')
}

// ─── Tolgee API ───────────────────────────────────────────

async function findKeyByName(config, keyName) {
  const url = new URL(`${config.apiUrl}/${config.apiVersion}/projects/translations`)

  url.searchParams.set('ak', config.apiKey)
  url.searchParams.set('size', '1')
  url.searchParams.set('languages', 'en-GB')
  url.searchParams.append('filterKeyName', keyName)

  const res = await fetch(url)

  if (!res.ok) {
    const body = await res.text()
    logError(`Tolgee API responded with ${res.status}: ${body}`)
    process.exit(1)
  }

  const data = await res.json()
  const keys = data?._embedded?.keys

  if (!keys?.length) {
    logError(`Key "${keyName}" not found in Tolgee.`)
    process.exit(1)
  }

  return keys[0]
}

async function complexUpdate(config, keyId, body) {
  const url = `${config.apiUrl}/${config.apiVersion}/projects/${config.projectId}/keys/${keyId}/complex-update`

  const res = await fetch(url, {
    method: 'PUT',
    headers: {
      'X-API-Key': config.apiKey,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(body)
  })

  const data = await res.json().catch(() => null)

  if (!res.ok) {
    const msg = data?.message || data?.error || res.statusText
    logError(`Tolgee API responded with ${res.status}: ${msg}`)
    if (data) console.error('  Response:', JSON.stringify(data, null, 2))
    process.exit(1)
  }

  return data
}

// ─── Main ─────────────────────────────────────────────────

async function main() {
  const env = loadEnv()
  const config = getConfig(env)
  const { keyName, newName, translations, tags } = parseArgs()

  log(`Looking up key "${keyName}"...`)
  const key = await findKeyByName(config, keyName)
  log(`Found key (id: ${key.keyId})`)

  // Build update body — name is required by the API
  const body = {
    name: newName || key.keyName
  }

  if (Object.keys(translations).length) {
    body.translations = {}
    for (const [locale, value] of Object.entries(translations)) {
      body.translations[locale] = laravelToTolgee(value)
    }
  }

  if (tags !== null) {
    body.tags = tags
  }

  // Log what we're updating
  if (newName) log(`  Rename: ${key.keyName} -> ${newName}`)
  if (body.translations) {
    for (const [locale, value] of Object.entries(body.translations)) {
      log(`  ${locale}: "${value}"`)
    }
  }
  if (tags !== null) log(`  Tags: ${tags.join(', ') || '(clear all)'}`)

  log('')
  const result = await complexUpdate(config, key.keyId, body)

  log(`Key updated successfully (id: ${result.id})`)
  log(`  Name: ${result.name}`)

  if (result.tags?.length) {
    log(`  Tags: ${result.tags.map(t => t.name).join(', ')}`)
  }

  if (result.translations) {
    for (const [locale, t] of Object.entries(result.translations)) {
      log(`  ${locale}: "${t.text}"`)
    }
  }
}

main().catch(err => {
  logError(err.message)
  process.exit(1)
})
