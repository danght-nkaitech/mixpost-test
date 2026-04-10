#!/usr/bin/env node

/**
 * Download a Heroicon from GitHub and create a Vue component.
 *
 * Usage:
 *   node scripts-dev/download-icon.js <icon-name> [--style=outline|solid|mini|micro]
 *
 * Examples:
 *   node scripts-dev/download-icon.js arrow-left                  # outline (default)
 *   node scripts-dev/download-icon.js photo --style=solid          # solid
 *   node scripts-dev/download-icon.js arrow-left --style=mini      # mini (20px solid)
 *
 * The icon name uses kebab-case as found on https://heroicons.com/
 * The output Vue component uses PascalCase (e.g. arrow-left -> ArrowLeft.vue).
 * Solid icons get a "Solid" suffix (e.g. photo --style=solid -> PhotoSolid.vue).
 */

import https from 'node:https'
import fs from 'node:fs'
import path from 'node:path'
import readline from 'node:readline'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const ICONS_DIR = path.resolve(__dirname, '..', 'resources', 'js', 'Icons')

const VALID_STYLES = ['outline', 'solid', 'mini', 'micro']

const SIZE_MAP = {
  outline: '24/outline',
  solid: '24/solid',
  mini: '20/solid',
  micro: '16/solid'
}

function toPascalCase(kebab) {
  return kebab
    .split('-')
    .map(part => part.charAt(0).toUpperCase() + part.slice(1))
    .join('')
}

function download(url) {
  return new Promise((resolve, reject) => {
    https
      .get(url, res => {
        if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
          return download(res.headers.location).then(resolve, reject)
        }

        let data = ''
        res.on('data', chunk => (data += chunk))
        res.on('end', () => resolve({ statusCode: res.statusCode, body: data }))
      })
      .on('error', reject)
  })
}

function confirm(question) {
  const rl = readline.createInterface({ input: process.stdin, output: process.stdout })
  return new Promise(resolve => {
    rl.question(question, answer => {
      rl.close()
      resolve(/^y(es)?$/i.test(answer.trim()))
    })
  })
}

async function main() {
  const args = process.argv.slice(2)
  let name = ''
  let style = 'outline'

  for (const arg of args) {
    if (arg.startsWith('--style=')) {
      style = arg.split('=')[1]
    } else if (arg.startsWith('-')) {
      console.error(`Unknown option: ${arg}`)
      process.exit(1)
    } else if (!name) {
      name = arg
    } else {
      console.error(`Unexpected argument: ${arg}`)
      process.exit(1)
    }
  }

  if (!name) {
    console.log(
      'Usage: node scripts-dev/download-icon.js <icon-name> [--style=outline|solid|mini|micro]'
    )
    console.log('')
    console.log('Browse icons at: https://heroicons.com/')
    process.exit(1)
  }

  if (!VALID_STYLES.includes(style)) {
    console.error(`Invalid style '${style}'. Use: ${VALID_STYLES.join(', ')}`)
    process.exit(1)
  }

  const url = `https://raw.githubusercontent.com/tailwindlabs/heroicons/master/optimized/${SIZE_MAP[style]}/${name}.svg`
  console.log(`Downloading: ${url}`)

  const res = await download(url)

  if (res.statusCode !== 200) {
    console.error(`Failed to download icon '${name}' (HTTP ${res.statusCode}).`)
    console.error('Check if the icon name is correct.')
    console.log('Browse icons at: https://heroicons.com/')
    process.exit(1)
  }

  const svg = res.body.trim()
  const pascalName = toPascalCase(name)
  const suffix = style === 'solid' ? 'Solid' : ''
  const componentName = `${pascalName}${suffix}`
  const filename = `${componentName}.vue`
  const filepath = path.join(ICONS_DIR, filename)

  if (fs.existsSync(filepath)) {
    const yes = await confirm(`File '${filename}' already exists. Overwrite? [y/N] `)
    if (!yes) {
      console.log('Aborted.')
      process.exit(0)
    }
  }

  const content = `<template>\n  ${svg}\n</template>\n`
  fs.writeFileSync(filepath, content)

  console.log('')
  console.log(`Icon saved to: resources/js/Icons/${filename}`)
  console.log('')
  console.log(`Import:  import ${componentName} from '@/Icons/${filename}'`)
}

main().catch(err => {
  console.error(err.message)
  process.exit(1)
})
