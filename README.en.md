# NFTDrive Official Decoder (PHP)

[日本語](./README.md) | **English**

A PHP decoder that fetches NFTDrive records from the Symbol blockchain, reassembles them,
and returns the original file.

> **These decoders are reference implementations for reading NFTDrive records.**
> They come with no warranty of correctness, security, or availability. If you install or
> publish one, read the [Security notes](#security-notes-read-before-deploying) below and
> modify the code as you see fit.

You are free to use and modify them. If you publish a decoder that includes this code,
please review the [Terms of Use](https://nft-drive.localinfo.jp/posts/23874701) (Japanese).

[NFTDrive Inc.](https://nftdrive.net)

---

## Which file to use

| File | Target | When to use |
|---|---|---|
| [`download-v3.php`](./download-v3.php) | **BEYOND / NFTDrive-v3 edition** | For new installations. Also reads data recorded with BEYOND |
| [`download.php`](./download.php) | Legacy edition | If you already run it and do not want its behavior to change. Reads only the legacy format (Base64, plaintext) |

`download-v3.php` is based on `download.php` and **returns legacy-format records exactly the same way**.
To switch, rename `download-v3.php` to `download.php` and overwrite the old file; callers keep the same URL.

---

## Differences from the legacy edition

### Response per storage format

Both editions were run against the same set of records.

| Record format | Legacy `download.php` | v3 edition `download-v3.php` |
|---|---|---|
| Base64, plaintext (`data:…`) | ✅ Original file | ✅ Original file (same) |
| **Binary, plaintext** (`nftdrive;binary/<MIME>`) | ❌ Corrupted content as `text/plain`: prefixed with the header string, with a `0x00` byte mixed in per chunk | ✅ Original file, with the MIME type from slot 15 |
| **Encrypted, Base64** (`BYNDE1.…`) | △ Ciphertext as `text/plain`, but with `0x00` bytes at the start and between chunks | ✅ Ciphertext as `text/plain`, unchanged |
| **Encrypted, legacy** (`U2FsdGVkX1…`) | △ Same as above (`0x00` bytes mixed in) | ✅ Ciphertext as `text/plain`, unchanged |
| **Encrypted, binary** (`nftdrive;binary/encrypted;<MIME>`) | ❌ Corrupted content as `text/plain` | ✅ The envelope as is: `application/octet-stream` for BYNDB1, `text/plain` for older BYNDE1 records |
| Unrecognized format | △ `text/plain` (`0x00` bytes mixed in) | △ `text/plain` (content as is) |
| **Missing blocks** | ❌ Returns the incomplete data with `200` | ✅ Refuses with `503` and lists the missing block numbers |

### Feature differences

| Item | Legacy edition | v3 edition |
|---|---|---|
| Format detection | None (only checks whether the data contains the string `base64`) | By block 0, slot 15 |
| Leading `0x00` of each message | Kept when concatenating | Stripped before concatenating |
| Reporting the returned format | None | `X-NFTDrive-Format` header ([Responses](#responses-v3-edition)) |
| Original MIME type of encrypted data | Unknown | Returned in `X-NFTDrive-Mime` for encrypted binary |
| Missing-block detection | None | Yes (`503`) |
| Decryption | No | No ([About encrypted data](#about-encrypted-data)) |
| Node entries | Host name only (`http://<host>:3000`) | Host name, or a full URL such as `https://host:3001` |
| Testnet node list | Outdated (`test02.xymnodes.com` and others) | Current six nodes |
| When the blacklist cannot be fetched | Aborts (`count(null)` throws a TypeError on PHP 8) | Continues without the blacklist check |
| Per-MIME handling (glb, bvh, ATNFT, generative images, host-specific video wrapper) | Yes | Same (unchanged) |
| SSRF and other security notes | Not addressed | Not addressed (same; see [Security notes](#security-notes-read-before-deploying)) |

---

## Installation

Place the file you want to use (`download-v3.php` or `download.php`) on a web server that runs PHP
(Apache, XAMPP, etc.).

| Requirement | Used for |
|---|---|
| PHP 8.x | The v3 edition was tested on PHP 8.2. 7.x will likely work but is untested |
| `curl` extension | Querying nodes |
| `fileinfo` extension | MIME detection for ATNFT (records that point to a URL) |
| `gd` extension | Compositing generative images |

### Nodes

List the nodes to use in `$node_list` at the top of the file.

```php
$node_list = [
  "example-node1.com",              // Host name only: http://<host>:3000
  "https://example-node2.net:3001"  // Full URL (v3 edition only)
];
```

- Mainnet uses the `$node_list` at the top
- Testnet (addresses that do not start with `N`) uses the testnet list further down in the file
- In both cases, unreachable nodes are skipped and the next one is tried

### Maximum page size

Set `$restCount` to the maximum number of items the node's REST API returns per page. This is usually 100.

```php
$restCount = 100;
```

---

## Usage

Pass a mosaic ID or a data address.

```
download-v3.php?id={MOSAIC_ID}
download-v3.php?address={DATA_ADDRESS}
```

Plaintext records are returned as the original file with its MIME type, so you can use the URL
directly, for example `<img src="download-v3.php?address=...">`.

---

## Responses (v3 edition)

The format is determined by the beginning of the 15th slot (0-based) of block 0.
The `X-NFTDrive-Format` header tells you what was returned.

| Start of slot 15 | `X-NFTDrive-Format` | Content-Type | Body |
|---|---|---|---|
| `data:` | `base64` | Original MIME type | Original file |
| `nftdrive;binary/<MIME>` | `binary` | Original MIME type | Original file |
| `BYNDE1.` | `bynde1` | `text/plain` | The `BYNDE1.…` envelope string |
| `U2FsdGVkX1` | `legacy` | `text/plain` | The `U2FsdGVkX1…` ciphertext |
| `nftdrive;binary/encrypted;<MIME>` | `binary-encrypted` | `application/octet-stream` (BYNDB1) / `text/plain` (BYNDE1) | The envelope itself |
| Anything else | `unknown` | `text/plain` | The concatenated content |
| (missing blocks) | `incomplete` | `text/plain` (status 503) | The missing block numbers |

Encrypted binary responses also carry:

| Header | Content |
|---|---|
| `X-NFTDrive-Envelope` | `BYNDB1` / `BYNDE1` / `unknown` |
| `X-NFTDrive-Mime` | MIME type of the file after decryption |

To read these headers from browser JavaScript, you need CORS headers
(`Access-Control-Allow-Origin` and `Access-Control-Expose-Headers`).
The relevant lines are commented out in the file; enable them if needed.

Error messages in the response body are in Japanese.

---

## About encrypted data

**Neither decoder decrypts.** Ciphertext is returned as is; decryption is the client's responsibility.

Decrypting on the server would require sending the password to the server, and putting it in
the URL would also leave it in access logs. This design avoids that.

The NFTDrive standalone viewer
([`sample/` in SymbolTransactionFetcher](https://github.com/bootarou/SymbolTransactionFetcher/tree/main/sample))
is the reference implementation of decryption (envelope structure, key slots, AAD).
Files that have a shared password (`password` slot) can be opened in that viewer, entirely in the browser.

> **Do not build anything that asks users for their recovery phrase or Address Master Key.**
> For files without a shared password, direct users to open them in the BEYOND app.

---

## Security notes (read before deploying)

Both decoders process data on your server that **anyone can write to the chain**.
Deploy only after you understand the behavior below. Whether to change it is up to you.

### 1. Fetching external URLs (SSRF)

- **ATNFT:** If the reassembled content starts with `h` and contains `http`, the server fetches
  that URL with `file_get_contents()` and returns the result.
- **Generative images:** A string taken from the content is passed directly to `imagecreatefrompng()`.

Whoever writes the record can **make your server access any URL, including your internal network
and cloud metadata endpoints**. On a public server, consider limiting fetches to external `https://`
addresses, or removing this processing.

### 2. Serving HTML and scripts

Records of type `text/html` or `image/svg+xml` are returned as is, **as content of your domain**.
Scripts inside them run on that domain. Serve the decoder from a dedicated domain, separate from
any site with logins or similar features.

### 3. Temporary files

Base64 content is written to a temporary file in the decoder's own directory and read back.
This needs write permission, and files may be left behind if processing stops midway.

### 4. Memory and time

The whole record is held in memory as a PHP string. Large files will hit `memory_limit` and
`max_execution_time` (set to 600 seconds in the file). In the v3 edition, binary records are
converted to Base64 before being passed to the legacy processing, so they use several times the
file's size in memory.

### 5. Blacklist

Each request fetches `https://nftdrive.net/black_list/`, and listed IDs and addresses are not returned.
If the list cannot be fetched, the legacy edition aborts and the v3 edition continues without the check.

### 6. Host-specific behavior

Only on `nftdrive-explorer.info` / `nft-drive` / `nftdrive-ex.net`, audio and video are wrapped
in HTML that discourages downloading. On any other host, the original file is returned.

---

## How it works (v3 edition)

1. Fetch every page of confirmed transactions sent to the data address
2. For each aggregate transaction (type 16705), read the messages of its inner transfers (type 16724)
   - Slot 0 is the block number; slots 1–14 of block 0 are metadata
   - Strip the leading `0x00` of each message
3. Sort by block number and confirm that no block is missing
4. Determine the format from slot 15 and concatenate the data part
   - Base64 storage: from slot 15 in block 0, from slot 1 in later blocks
   - Binary storage: from slot 16 in block 0, from slot 1 in later blocks
5. Respond according to the format ([Responses](#responses-v3-edition))

The detailed format specifications are `docs/decoder-migration-guide.md` (decoder migration guide)
and `docs/file-envelope-spec.md` (envelope specification) in the BEYOND repository (Japanese).
