[<img src="./art/logo.svg" alt="Logo Mixpost" />](https://mixpost.app)

## Welcome to Mixpost Pro

Mixpost it's the coolest Self-hosted social media management software.

Join our community:

- [Discord](https://mixpost.app/discord)
- [Facebook Private Group](https://www.facebook.com/groups/getmixpost)

## Installation

Read our [documentation](https://docs.mixpost.app/pro/) on how to get started.

## Development

### Code Quality Tools

#### PHP Tools

##### Laravel Pint (Code Formatting)
```bash
composer format
```

##### PHPStan (Static Analysis)
For static analysis, we use PHPStan with Larastan. To run the analysis:

```bash
composer analyse
```

**Note for developers:** The `phpstan.neon` file is provided as a base configuration. You should copy it to `phpstan.neon.dist` or create your own local configuration file if you need custom rules or settings for your development environment. The base configuration uses level 5 analysis.

#### JavaScript/Vue Tools

##### ESLint (Linting)
ESLint is configured to automatically fix code style issues:

```bash
# Run ESLint with auto-fix
npm run lint

# Check for issues without fixing
npm run lint:check
```

##### Prettier (Code Formatting)
Prettier formats JavaScript, Vue, and CSS files:

```bash
# Format files
npm run format

# Check formatting without making changes
npm run format:check
```

#### Git Hooks (Husky)

This project uses Husky to run quality checks before commits. When you commit changes:

1. **Pre-commit hook** automatically runs:
   - ESLint and Prettier on staged JavaScript/Vue/CSS files
   - Laravel Pint on staged PHP files

To set up Husky after cloning the repository:

```bash
npm install
```

The hooks are configured in:
- `.husky/pre-commit` - Runs lint-staged
- `package.json` - Contains lint-staged configuration for file-specific commands

### PHPStorm IDE Configuration

#### Laravel Pint
1. Go to **Settings/Preferences** → **PHP** → **Quality Tools**
2. Expand **Laravel Pint** section
3. Switch to **ON** mode
4. Select **By default project interpreter** for Configuration
5. Select **defined in pint.json** for Ruleset.
6. Go to **PHP** → **Quality Tools**, and choose **Laravel Pint** as the code formatter
7. Save

#### ESLint
1. Go to **Settings/Preferences** → **Languages & Frameworks** → **JavaScript** → **Code Quality Tools** → **ESLint**
2. Select **Automatic ESLint configuration**
3. Save

#### Prettier
1. Go to **Settings/Preferences** → **Languages & Frameworks** → **JavaScript** → **Prettier**
2. Select **Automatic Prettier configuration**
3. Enable **Runs on save**
4. Save

### Testing

```bash
composer test
```

## Changelog

Please see [Releases](../../releases) for more information what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.
