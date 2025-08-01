# MDS Documentation

## Overview

This directory contains documentation for the MDS (Museum Data Services) project.

## Documents

- **[messenger-single-exchange.md](messenger-single-exchange.md)**: Detailed documentation about the single exchange messenger configuration, including dynamic routing middleware usage.

## Key Concepts

### Messaging Architecture
MDS uses a single exchange (`mds_main`) with multiple queues for processing different types of museum data operations. This architecture provides:

- Efficient resource usage
- Flexible message routing
- Granular consumption control
- Dynamic transport selection via middleware

### Transport Selection
The system supports runtime transport selection using `TransportNamesStamp`, which is converted to appropriate routing keys by the Dynamic Routing Middleware.

## Quick Reference

### Important DSN Configuration
**Critical**: Use `phpamqplib://` protocol for the messenger DSN to ensure compatibility with the Dynamic Routing Middleware:

```bash
MESSENGER_TRANSPORT_DSN_RABBITMQ=phpamqplib://guest:guest@localhost:5672/mds
```

**Do NOT use**: `amqp://` protocol as it defaults to Symfony's built-in AMQP transport which has limited routing capabilities.

### Available Transports
- `extract_fetch`: Data extraction and fetching operations
- `extract_load`: Data extraction and loading operations  
- `grp_extract`: Group data extraction operations
- `meili`: MeiliSearch indexing operations
- `async`: General asynchronous operations

### Validation Command
Test the routing system:
```bash
php bin/console app:validate-routing
```
