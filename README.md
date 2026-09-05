<div class="filament-hidden">

![plump-cli](https://raw.githubusercontent.com/jeffersongoncalves/plump-cli/main/art/jeffersongoncalves-plump-cli.png)

</div>

# plump-cli

[![Buy Me A Coffee](https://img.shields.io/badge/Buy%20Me%20A%20Coffee-support-FFDD00?style=flat-square&logo=buy-me-a-coffee&logoColor=black)](https://buymeacoffee.com/jeffersongoncalves)

A small CLI to query [Plumb](https://plumbphp.dev) — mechanically-scored Security, Maintenance,
and Ecosystem Health for PHP/Composer packages — directly from the terminal. Show a package's
scores, trigger a fresh scan, or browse scan history. No API key required.

Built with [Laravel Zero](https://laravel-zero.com).

<p align="center">
  <a href="https://github.com/jeffersongoncalves/plump-cli/actions"><img src="https://github.com/jeffersongoncalves/plump-cli/actions/workflows/run-tests.yml/badge.svg" alt="Tests" /></a>
  <a href="https://packagist.org/packages/jeffersongoncalves/plump-cli"><img src="https://img.shields.io/packagist/dt/jeffersongoncalves/plump-cli" alt="Total Downloads" /></a>
  <a href="https://github.com/jeffersongoncalves/plump-cli/blob/main/LICENSE"><img src="https://img.shields.io/github/license/jeffersongoncalves/plump-cli" alt="License" /></a>
  <img src="https://img.shields.io/badge/php-%3E%3D8.2-8892BF" alt="PHP 8.2+" />
</p>

## Installation

```bash
composer global require jeffersongoncalves/plump-cli
```

Or download the PHAR from the [releases page](https://github.com/jeffersongoncalves/plump-cli/releases).

## Rate limits

Plumb's public API enforces these limits per IP; `plump` surfaces `429`/`503` responses
(including `Retry-After`) as a friendly error instead of a raw HTTP failure:

| Endpoint | Limit |
|----------|-------|
| GET (`show`, `history`) | 120 requests/minute |
| POST (`scan`) | 3 requests/15 minutes |

## Commands

### `show` — package scores and latest scan

```bash
plump show laravel/framework
```

### `scan` — trigger a scan

```bash
plump scan laravel/framework
```

Queues an asynchronous scan (`202`) or returns the already-fresh cached result (`200`).
Heavily rate limited server-side — see above.

### `history` — scan history

```bash
plump history laravel/framework
plump history laravel/framework --sort=-scanned_at --limit=50
```

## Development

```bash
composer install
composer test        # pest + pint --test
composer build       # build the PHAR into builds/
```

## License

MIT © Jefferson Gonçalves
