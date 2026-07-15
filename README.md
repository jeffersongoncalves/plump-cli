# plump-cli

A small CLI to query [Plumb](https://plumbphp.dev) — mechanically-scored Security, Maintenance,
and Ecosystem Health for PHP/Composer packages — directly from the terminal. Show a package's
scores, trigger a fresh scan, or browse scan history. No API key required.

Built with [Laravel Zero](https://laravel-zero.com).

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
