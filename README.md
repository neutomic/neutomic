# **Neu**tomic

![Unit tests status](https://github.com/neutomic/neutomic/workflows/unit%20tests/badge.svg)
![Static analysis status](https://github.com/neutomic/neutomic/workflows/static%20analysis/badge.svg)
![Security analysis status](https://github.com/neutomic/neutomic/workflows/security%20analysis/badge.svg)
![Coding standards status](https://github.com/neutomic/neutomic/workflows/coding%20standards/badge.svg)
[![Coverage Status](https://coveralls.io/repos/github/neutomic/neutomic/badge.svg)](https://coveralls.io/github/neutomic/neutomic)
[![Total Downloads](https://poser.pugx.org/neutomic/neutomic/d/total.svg)](https://packagist.org/packages/neutomic/neutomic)
[![Latest Stable Version](https://poser.pugx.org/neutomic/neutomic/v/stable.svg)](https://packagist.org/packages/neutomic/neutomic)
[![License](https://poser.pugx.org/neutomic/neutomic/license.svg)](https://packagist.org/packages/neutomic/neutomic)

## Introduction

> [!CAUTION]
> **Neu**tomic is currently in early development and is not yet ready for production use. Please check back later for updates.

Neutomic is a fast, asynchronous, lightweight PHP framework tailored for long-running process environments.

Key components of Neutomic include:

- **Event-Driven Architecture**: Built on top of [revolt-php](https://revolt.run/), Neu supports event-driven, non-blocking I/O operations, ensuring efficient handling of concurrent tasks.
- **Non-Blocking HTTP Server**: It leverages [amphp/http-server](https://amphp.org/http-server), a non-blocking HTTP/1.1 and HTTP/2 server, enabling fast, scalable web applications.

Neu is designed to power high-traffic APIs, complex web applications, and real-time data processing systems. By adopting modern asynchronous PHP features, Neu helps developers build robust, efficient applications that are well-suited to today's dynamic, high-load environments.

## Demo

Look at the demo repository [neutomic/demo](https://github.com/neutomic/demo) for a simple example of a Neu application.

## To Do

- [x] Multithreading support
- [x] Static file serving
- [x] Middleware support
- [x] Dependency injection
- [x] Database support
- [x] Logging
- [x] Configuration
- [x] Error handling
- [x] Cache
- [x] Console
- [x] Templating
- [x] Logging
- [x] Database
  - [x] MySQL
  - [x] PostgreSQL
- [x] Session management
- [x] Cookie management
- [x] Event Dispatcher
- [x] Console
- [x] Cache
  - [x] File
  - [x] Redis
  - [x] Locale
- [ ] Form Parsing
    - [x] Incremental parsing
        - [x] Multipart
        - [x] URL-encoded
    - [ ] Full parsing
        - [ ] Multipart
        - [ ] URL-encoded
- [ ] CSRF protection
- [ ] Authentication
- [ ] Authorization
- [ ] WebSockets support
- [ ] Unit tests
- [ ] Code coverage
- [ ] Static analysis
- [ ] Security analysis

## License

The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information.
