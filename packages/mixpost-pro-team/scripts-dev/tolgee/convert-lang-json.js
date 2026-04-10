#!/usr/bin/env node

//
// Convert PHP translation files to JSON files for the Vue frontend.
//
// Usage:
//   npm run convert-lang-json
//
// Reads:  resources/lang/{locale}/*.php
// Writes: resources/lang-json/{locale}.json
//

import { execSync } from 'node:child_process'
import {
  readdirSync,
  existsSync,
  mkdirSync,
  writeFileSync,
  readFileSync,
  unlinkSync
} from 'node:fs'
import { resolve, dirname, basename, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { tmpdir } from 'node:os'

const __dirname = dirname(fileURLToPath(import.meta.url))
const ROOT = resolve(__dirname, '../..')

const LANG_DIR = resolve(ROOT, 'resources/lang')
const LANG_JSON_DIR = resolve(ROOT, 'resources/lang-json')

const SKIP_FILES = ['backend.php', 'mail.php', 'rules.php']
const SKIP_KEYS = ['backend']

// ─── Logging ──────────────────────────────────────────────

function log(msg) {
  console.log(`[convert-lang-json] ${msg}`)
}

function logError(msg) {
  console.error(`[convert-lang-json] ERROR: ${msg}`)
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

// ─── PHP File Reading ─────────────────────────────────────

function getPhpFiles(locale) {
  const dir = resolve(LANG_DIR, locale)

  if (!existsSync(dir)) return []

  return readdirSync(dir)
    .filter(f => f.endsWith('.php'))
    .filter(f => !SKIP_FILES.includes(f))
    .filter(f => !f.endsWith('.json'))
    .map(f => basename(f, '.php'))
    .filter(name => !name.endsWith('_back'))
}

function readAllPhpFiles(localeGroups) {
  // Build a PHP script that reads all files in one process
  const files = []

  for (const [locale, groups] of Object.entries(localeGroups)) {
    for (const group of groups) {
      files.push({ locale, group, path: resolve(LANG_DIR, locale, `${group}.php`) })
    }
  }

  const lines = ['<?php', `$r=array_fill(0,${files.length},'{}');`]
  for (let i = 0; i < files.length; i++) {
    lines.push(`$r[${i}]=json_encode(require '${files[i].path}');`)
  }
  lines.push('echo json_encode($r);')

  const tmpFile = join(tmpdir(), `convert-lang-${Date.now()}.php`)
  writeFileSync(tmpFile, lines.join('\n'), 'utf-8')

  try {
    const raw = execSync(`php ${tmpFile}`, {
      encoding: 'utf-8',
      maxBuffer: 50 * 1024 * 1024
    })

    const results = JSON.parse(raw)
    const parsed = {}

    for (let i = 0; i < files.length; i++) {
      const { locale, group } = files[i]
      if (!parsed[locale]) parsed[locale] = {}

      try {
        parsed[locale][group] = JSON.parse(results[i])
      } catch {
        logError(`Failed to parse ${locale}/${group}.php, skipping.`)
        parsed[locale][group] = {}
      }
    }

    return parsed
  } catch (err) {
    logError(`PHP batch read failed: ${err.message}`)
    process.exit(1)
  } finally {
    try {
      unlinkSync(tmpFile)
    } catch {
      /* ignore */
    }
  }
}

// ─── String Conversion ───────────────────────────────────

function adjustStringWithContext(s) {
  if (typeof s !== 'string') return s

  // Single-pass: handle mailto:, tel:, !:variable (escaped), and :variable
  return s.replace(/(mailto|tel|!)?:(\w+)/g, (match, prefix, name) => {
    if (prefix === 'mailto' || prefix === 'tel') return match // preserve mailto:/tel:
    if (prefix === '!') return `:${name}` // !:var -> :var (remove escape)
    return `{${name}}` // :var -> {var}
  })
}

// ─── Data Processing ──────────────────────────────────────

function adjustObject(obj) {
  const result = {}

  for (const [key, value] of Object.entries(obj)) {
    const adjustedKey = adjustStringWithContext(key)

    if (value !== null && typeof value === 'object') {
      result[adjustedKey] = adjustObject(value)
    } else {
      result[adjustedKey] = adjustStringWithContext(value)
    }
  }

  return result
}

function filterEmpty(obj) {
  const result = {}

  for (const [key, value] of Object.entries(obj)) {
    if (value !== null && typeof value === 'object') {
      const filtered = filterEmpty(value)
      if (Object.keys(filtered).length) {
        result[key] = filtered
      }
    } else if (value) {
      result[key] = value
    }
  }

  return result
}

function removeKeys(obj, keysToSkip) {
  const result = {}

  for (const [key, value] of Object.entries(obj)) {
    if (keysToSkip.includes(key)) continue

    if (value !== null && typeof value === 'object') {
      result[key] = removeKeys(value, keysToSkip)
    } else {
      result[key] = value
    }
  }

  return result
}

// ─── Main ─────────────────────────────────────────────────

function main() {
  log('Converting PHP lang files to JSON...\n')

  const locales = getLocales()
  log(`Locales (${locales.length}): ${locales.join(', ')}\n`)

  if (!existsSync(LANG_JSON_DIR)) {
    mkdirSync(LANG_JSON_DIR, { recursive: true })
  }

  // Collect all files per locale
  const localeGroups = {}
  for (const locale of locales) {
    localeGroups[locale] = getPhpFiles(locale)
  }

  // Read all PHP files in a single process
  const allData = readAllPhpFiles(localeGroups)

  let totalFiles = 0

  for (const locale of locales) {
    const groups = localeGroups[locale]
    const jsonData = {}

    for (const group of groups) {
      const filtered = filterEmpty(allData[locale]?.[group] ?? {})

      if (Object.keys(filtered).length) {
        jsonData[group] = filtered
      }
    }

    const adjusted = adjustObject(jsonData)
    const cleaned = removeKeys(adjusted, SKIP_KEYS)

    const outputPath = resolve(LANG_JSON_DIR, `${locale}.json`)
    // Escape forward slashes to match PHP json_encode output
    const json = JSON.stringify(cleaned, null, 4).replace(/\//g, '\\/')
    writeFileSync(outputPath, json, 'utf-8')

    log(`  ${locale}.json (${groups.length} files)`)
    totalFiles++
  }

  log(`\nDone! Converted ${totalFiles} locale(s) to JSON.`)
}

main()
