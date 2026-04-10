#!/usr/bin/env node

//
// Create a new translation key in Tolgee.
//
// Usage:
//   npm run create-key -- <keyName> "locale=value" ... [--branch=name] [--tag=extra,tags]
//
// Examples:
//   npm run create-key -- service.mastodon.connect "en-GB=Connect Mastodon" "fr-FR=Connecter Mastodon"
//   npm run create-key -- service.mastodon.connect "en-GB=Connect Mastodon" --branch=feature-x
//   npm run create-key -- service.mastodon.connect "en-GB=Hello :name" --tag=extra-tag
//
// The key is automatically tagged with draft:{current-branch}.
// Laravel :variable syntax is converted to Tolgee {variable} format.
//

import { execSync } from 'node:child_process'
import { readFileSync, existsSync } from 'node:fs'
import { resolve, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = dirname(fileURLToPath(import.meta.url))
const ROOT = resolve(__dirname, '../..')

// ─── Logging ──────────────────────────────────────────────

function log(msg) {
  console.log(`[create-key] ${msg}`)
}

function logError(msg) {
  console.error(`[create-key] ERROR: ${msg}`)
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

// ─── Git ──────────────────────────────────────────────────

function getCurrentBranch() {
  try {
    return execSync('git rev-parse --abbrev-ref HEAD', { cwd: ROOT, encoding: 'utf-8' }).trim()
  } catch {
    logError('Failed to detect current git branch.')
    process.exit(1)
  }
}

// ─── CLI Arguments ────────────────────────────────────────

function parseArgs() {
  const args = process.argv.slice(2)
  let keyName = null
  let branch = null
  const extraTags = []
  const translations = {}

  for (const arg of args) {
    if (arg.startsWith('--branch=')) {
      branch = arg.slice('--branch='.length)
    } else if (arg.startsWith('--tag=')) {
      extraTags.push(...arg.slice('--tag='.length).split(','))
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
      'Usage: npm run create-key -- <keyName> "locale=value" ... [--branch=name] [--tag=extra,tags]'
    )
    console.error(
      'Example: npm run create-key -- service.mastodon.connect "en-GB=Connect Mastodon"'
    )
    process.exit(1)
  }

  if (!Object.keys(translations).length) {
    logError('At least one translation is required (e.g. "en-GB=Hello").')
    process.exit(1)
  }

  return { keyName, translations, branch, extraTags }
}

// ─── Translation Formatting ──────────────────────────────

function toLarvelToTolgee(text) {
  // Convert Laravel :variable to Tolgee {variable}
  return text.replace(/:(\w+)/g, '{$1}')
}

// ─── Tolgee API ───────────────────────────────────────────

async function createKey(config, body) {
  const url = `${config.apiUrl}/${config.apiVersion}/projects/${config.projectId}/keys`

  const res = await fetch(url, {
    method: 'POST',
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
  const { keyName, translations, branch, extraTags } = parseArgs()

  const currentBranch = branch || getCurrentBranch()
  const tags = [`draft:${currentBranch}`, ...extraTags]

  // Convert translations to Tolgee format
  const tolgeeTranslations = {}
  for (const [locale, value] of Object.entries(translations)) {
    tolgeeTranslations[locale] = toLarvelToTolgee(value)
  }

  log(`Key:          ${keyName}`)
  log(`Tags:         ${tags.join(', ')}`)
  log(
    `Translations: ${Object.entries(tolgeeTranslations)
      .map(([l, v]) => `${l}="${v}"`)
      .join(', ')}`
  )
  log('')

  const body = {
    name: keyName,
    translations: tolgeeTranslations,
    tags,
    isPlural: false
  }

  const result = await createKey(config, body)

  log(`Key created successfully (id: ${result.id})`)
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
