#!/usr/bin/env node

import { execSync } from 'node:child_process'
import { readFileSync, writeFileSync, existsSync, mkdirSync, unlinkSync } from 'node:fs'
import { resolve, dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { tmpdir } from 'node:os'

const __dirname = dirname(fileURLToPath(import.meta.url))
const ROOT = resolve(__dirname, '../..')

// ─── Logging ──────────────────────────────────────────────

function log(msg) {
  console.log(`[sync-translations] ${msg}`)
}

function logError(msg) {
  console.error(`[sync-translations] ERROR: ${msg}`)
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

function parseBranchArg() {
  for (const arg of process.argv.slice(2)) {
    if (arg.startsWith('--branch=')) return arg.slice('--branch='.length)
  }
  return null
}

// ─── Locales ──────────────────────────────────────────────

function getLocales() {
  const content = readFileSync(resolve(ROOT, 'config/mixpost.php'), 'utf-8')
  const locales = []
  let match

  const re = /'long'\s*=>\s*'([^']+)'/g
  while ((match = re.exec(content)) !== null) {
    locales.push(match[1])
  }

  if (!locales.length) {
    logError('No locales found in config/mixpost.php')
    process.exit(1)
  }

  return locales
}

// ─── Tolgee API ───────────────────────────────────────────

async function fetchPage(config, languages, tag, page = 0) {
  const url = new URL(
    `${config.apiUrl}/${config.apiVersion}/projects/${config.projectId}/translations`
  )

  url.searchParams.set('ak', config.apiKey)
  url.searchParams.set('languages', languages.join(','))
  url.searchParams.set('size', '1000')
  url.searchParams.set('page', String(page))
  url.searchParams.append('filterTag', tag)

  const res = await fetch(url)

  if (!res.ok) {
    const body = await res.text()
    logError(`Tolgee API responded with ${res.status}: ${body}`)
    process.exit(1)
  }

  return res.json()
}

async function fetchAllPages(config, locales, tag) {
  log(`Fetching translations tagged "${tag}"...`)

  const first = await fetchPage(config, locales, tag, 0)
  const totalPages = first?.page?.totalPages ?? 1
  const totalElements = first?.page?.totalElements ?? 0

  log(`Found ${totalElements} key(s) across ${totalPages} page(s).`)

  const pages = [first]

  for (let p = 1; p < totalPages; p++) {
    log(`  Fetching page ${p + 1}/${totalPages}...`)
    pages.push(await fetchPage(config, locales, tag, p))
  }

  return pages
}

// ─── Translation Processing ──────────────────────────────

function excludeProductionKeys(pages) {
  let excluded = 0

  for (const page of pages) {
    const keys = page?._embedded?.keys
    if (!keys) continue

    page._embedded.keys = keys.filter(item => {
      const hasProduction = (item.keyTags || []).some(t => t.name === 'production')
      if (hasProduction) excluded++
      return !hasProduction
    })
  }

  if (excluded) {
    log(`Excluded ${excluded} key(s) tagged "production".`)
  }

  return pages
}

function parseTranslations(pages) {
  const translations = {}

  for (const page of pages) {
    const keys = page?._embedded?.keys
    if (!keys) continue

    for (const item of keys) {
      const dotIndex = item.keyName.indexOf('.')
      if (dotIndex === -1) continue

      const group = item.keyName.slice(0, dotIndex)
      const nestedKey = item.keyName.slice(dotIndex + 1)

      for (const [locale, translation] of Object.entries(item.translations)) {
        if (!translation?.text) continue

        // Convert Tolgee {variable} to Laravel :variable
        const value = translation.text.replace(/\{(\w+)\}/g, ':$1')

        if (!translations[locale]) translations[locale] = {}
        if (!translations[locale][group]) translations[locale][group] = {}

        // Build nested object from dot-notation key
        const parts = nestedKey.split('.')
        let current = translations[locale][group]

        for (let i = 0; i < parts.length - 1; i++) {
          if (!current[parts[i]] || typeof current[parts[i]] !== 'object') {
            current[parts[i]] = {}
          }
          current = current[parts[i]]
        }

        current[parts[parts.length - 1]] = value
      }
    }
  }

  return translations
}

// ─── PHP File I/O ────────────────────────────────────────

function readPhpFiles(filePaths) {
  const existing = filePaths.filter(f => existsSync(f))

  if (!existing.length) {
    return Object.fromEntries(filePaths.map(f => [f, {}]))
  }

  const lines = ['<?php', `$r=[];`]
  for (const f of existing) {
    lines.push(`$r['${f}']=json_encode(require '${f}');`)
  }
  lines.push('echo json_encode($r);')

  const tmpFile = join(tmpdir(), `sync-translations-${Date.now()}.php`)
  writeFileSync(tmpFile, lines.join('\n'), 'utf-8')

  const result = Object.fromEntries(filePaths.map(f => [f, {}]))

  try {
    const raw = execSync(`php ${tmpFile}`, {
      encoding: 'utf-8',
      maxBuffer: 50 * 1024 * 1024
    })
    const parsed = JSON.parse(raw)

    for (const [path, json] of Object.entries(parsed)) {
      try {
        result[path] = JSON.parse(json)
      } catch {
        logError(`Failed to parse ${path}, skipping.`)
      }
    }
  } catch (err) {
    logError(`PHP batch read failed: ${err.message}`)
  } finally {
    try {
      unlinkSync(tmpFile)
    } catch {
      /* empty */
    }
  }

  return result
}

function escapePhpString(value) {
  return String(value).replace(/\\/g, '\\\\').replace(/'/g, "\\'")
}

function toPhpArray(obj, indent = 4) {
  const pad = ' '.repeat(indent)
  const closePad = ' '.repeat(indent - 4)
  const entries = []

  for (const [key, value] of Object.entries(obj)) {
    const escaped = escapePhpString(key)

    if (value !== null && typeof value === 'object') {
      entries.push(`${pad}'${escaped}' => ${toPhpArray(value, indent + 4)}`)
    } else {
      entries.push(`${pad}'${escaped}' => '${escapePhpString(value)}'`)
    }
  }

  return `[\n${entries.join(',\n')},\n${closePad}]`
}

function writePhpFile(filePath, data) {
  const dir = dirname(filePath)
  if (!existsSync(dir)) mkdirSync(dir, { recursive: true })

  writeFileSync(filePath, `<?php\n\nreturn ${toPhpArray(data)};\n`, 'utf-8')
}

// ─── Deep Merge ──────────────────────────────────────────

function deepMerge(target, source) {
  const result = { ...target }

  for (const [key, value] of Object.entries(source)) {
    if (
      value !== null &&
      typeof value === 'object' &&
      typeof result[key] === 'object' &&
      result[key] !== null
    ) {
      result[key] = deepMerge(result[key], value)
    } else {
      result[key] = value
    }
  }

  return result
}

// ─── Change Counting ─────────────────────────────────────

function countChanges(existing, incoming) {
  let added = 0
  let updated = 0

  for (const [key, value] of Object.entries(incoming)) {
    if (value !== null && typeof value === 'object') {
      const sub = countChanges(existing?.[key] ?? {}, value)
      added += sub.added
      updated += sub.updated
    } else if (!(key in (existing ?? {}))) {
      added++
    } else if (existing[key] !== value) {
      updated++
    }
  }

  return { added, updated }
}

// ─── Main ─────────────────────────────────────────────────

async function main() {
  log('Starting translation sync...\n')

  const env = loadEnv()
  const config = getConfig(env)
  log(`API: ${config.apiUrl} (${config.apiVersion})`)

  const branch = parseBranchArg() || getCurrentBranch()
  const tag = `draft:${branch}`
  log(`Branch: ${branch}`)
  log(`Tag filter: ${tag}\n`)

  const locales = getLocales()
  log(`Locales (${locales.length}): ${locales.join(', ')}\n`)

  let pages = await fetchAllPages(config, locales, tag)
  pages = excludeProductionKeys(pages)

  const translations = parseTranslations(pages)
  const localesWithData = Object.keys(translations)

  if (!localesWithData.length) {
    log('\nNo translations found. Nothing to do.')
    return
  }

  log('')

  // Batch-read all existing PHP files in a single process
  const filePaths = []
  for (const locale of localesWithData) {
    for (const group of Object.keys(translations[locale])) {
      filePaths.push(resolve(ROOT, `resources/lang/${locale}/${group}.php`))
    }
  }
  const existingFiles = readPhpFiles(filePaths)

  let totalAdded = 0
  let totalUpdated = 0

  for (const locale of localesWithData) {
    for (const [group, newKeys] of Object.entries(translations[locale])) {
      const filePath = resolve(ROOT, `resources/lang/${locale}/${group}.php`)
      const existing = existingFiles[filePath]
      const { added, updated } = countChanges(existing, newKeys)

      writePhpFile(filePath, deepMerge(existing, newKeys))

      if (added || updated) {
        log(`  ${locale}/${group}.php  +${added} added  ~${updated} updated`)
      }

      totalAdded += added
      totalUpdated += updated
    }
  }

  log('')
  log('Done!')
  log(`  Added:   ${totalAdded}`)
  log(`  Updated: ${totalUpdated}`)
  log(`  Total:   ${totalAdded + totalUpdated}`)
}

main().catch(err => {
  logError(err.message)
  process.exit(1)
})
