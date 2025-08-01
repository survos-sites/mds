# Single Exchange Messenger Configuration

## Introduction

This document describes the single exchange messenger configuration for MDS, utilizing the `mds_main` exchange with multiple queues for granular consumption control.

## Architecture Overview

### Benefits
- **Simplified Management**: Single exchange reduces management complexity.
- **Resource Efficiency**: Easier on RabbitMQ resources.
- **Flexible Routing**: Routing keys allow selective message distribution.
- **Middleware Compatibility**: Works with dynamic routing middleware.

### Components

#### 1. Exchange Configuration
- **Name**: `mds_main`
- **Type**: `direct` (supports routing keys)
- **Durability**: `true` (exchange persists across RabbitMQ restarts)

#### 2. Transports
Each queue has its own transport for specific tasks:
- `extract_fetch`: Data extraction and fetching
- `extract_load`: Data extraction and loading
- `grp_extract`: Group data extraction
- `meili`: MeiliSearch indexing
- `async`: Asynchronous tasks

#### 3. Dynamic Routing Middleware
- **Functionality**: Converts `TransportNamesStamp` to `AmqpStamp` with routing keys.
- **Source**: `Survos\WorkflowBundle\Messenger\Middleware\DynamicRoutingMiddleware`
- **Purpose**: Allows runtime transport selection

## Usage

### Default Routing
```php
$message = new TransitionMessage(1, 'EntityName', 'transition', 'workflow');
$messageBus->dispatch($message);
```

### Override Transport
```php
$message = new TransitionMessage(1, 'EntityName', 'transition', 'workflow');
$messageBus->dispatch($message, [new TransportNamesStamp(['extract_fetch'])]);
```

### Consume Specific Transport
```bash
php bin/console messenger:consume extract_fetch --limit=10
```

## Configuration Details

### DSN Configuration
**Critical**: The transport DSN must use `phpamqplib://` protocol:

```bash
# ✅ Correct - Enables Dynamic Routing Middleware
MESSENGER_TRANSPORT_DSN_RABBITMQ=phpamqplib://guest:guest@localhost:5672/mds

# ❌ Incorrect - Uses Symfony AMQP (limited routing)
MESSENGER_TRANSPORT_DSN_RABBITMQ=amqp://guest:guest@localhost:5672/mds
```

**Why `phpamqplib://`?**
- Uses `jwage/phpamqplib-messenger` bundle
- Supports `durable: true` exchange option
- Compatible with DynamicRoutingMiddleware
- Full routing key support

### Messenger Configuration
- **File**: `config/packages/messenger.yaml`
- **Features**: DRY configuration using YAML anchors, durable exchange setup.
- **Exchange**: `mds_main` with `durable: true` for persistence

### Service Configuration
- **File**: `config/services.yaml` 
- **Features**: Proper tagging of dynamic routing middleware.

## Maintenance

### Setup Transports
```bash
php bin/console messenger:setup-transports
```

### Monitor Queues
```bash
php bin/console messenger:stats
```

### Retry Failed Messages
```bash
php bin/console messenger:failed:retry
```

