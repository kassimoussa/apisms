# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 11 SMS API Gateway application that provides a modern REST API interface for Kannel SMS server. Built with Livewire 3 for the admin dashboard, custom API authentication, and real-time SMS processing.

**Core Purpose**: Bridge between modern applications (like DPCR Fleet Management) and legacy Kannel SMS infrastructure.

## Quick Start

### Development Setup
```bash
# 1. Environment setup
cp .env.example .env
# Edit .env with your Kannel server credentials:
# - KANNEL_URL=http://your-kannel-server:13013/cgi-bin/sendsms  
# - KANNEL_USERNAME=your_username
# - KANNEL_PASSWORD=your_password
# - KANNEL_FROM=+253XXXXXXXX

# 2. Install dependencies  
composer install && npm install

# 3. Database setup
php artisan key:generate
php artisan migrate
php artisan db:seed --class=ClientSeeder  # Creates DPCR client

# 4. Test Kannel connectivity
php artisan sms:test +25377123456 --check-connectivity

# 5. Start development
composer run dev  # Starts all services concurrently
```

### Essential Commands

```bash
# SMS Testing
php artisan sms:test +25377123456                    # Send test SMS
php artisan sms:test +25377123456 --message="Hello"  # Custom message  
php artisan sms:test +25377123456 --check-connectivity # Test connection only

# Development  
composer run dev        # All services (server, queue, logs, Vite)
php artisan migrate     # Run database migrations
php artisan db:seed     # Seed with demo clients

# Production
php artisan config:cache && php artisan route:cache
```

## Architecture Overview

### SMS Flow
1. **Client Request** → API endpoint (`/api/v1/sms/send`) 
2. **Authentication** → Custom middleware validates API key
3. **Database** → SMS record created with 'pending' status
4. **Kannel** → HTTP GET request to send SMS
5. **Response** → SMS status updated, JSON response returned
6. **Webhooks** → DLR/MO callbacks update delivery status

### Core Components

#### Models & Database
- **Client**: API clients with rate limiting and IP restrictions
- **SmsMessage**: SMS records with status tracking and Kannel integration
- **DeliveryReport**: Webhook delivery reports from Kannel

#### API Layer
- **Authentication**: `AuthenticateApiClient` middleware with rate limiting
- **Endpoints**: RESTful SMS API with validation and error handling
- **Resources**: Structured JSON responses with proper formatting

#### Services
- **KannelService**: HTTP client for Kannel integration with retry logic
- **Validation**: Phone number validation for Djibouti (+253) format
- **Logging**: Comprehensive request/response logging

#### Admin Interface  
- **Livewire Dashboard**: Real-time statistics and monitoring
- **Client Management**: API key generation and client administration
- **SMS Testing**: Direct SMS sending interface for demos

## API Usage

### Authentication
All API requests require an API key in the header:
```bash
X-API-Key: sk_your_32_character_api_key
# OR
Authorization: Bearer sk_your_32_character_api_key  
```

### Send SMS
```bash
curl -X POST http://localhost:8000/api/v1/sms/send \
  -H "X-API-Key: sk_your_api_key" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+25377123456",
    "message": "Hello from ApiSMS Gateway!",
    "from": "+25377000000"
  }'
```

### Check SMS Status  
```bash
curl -H "X-API-Key: sk_your_api_key" \
  http://localhost:8000/api/v1/sms/123/status
```

### Get Statistics
```bash
curl -H "X-API-Key: sk_your_api_key" \
  http://localhost:8000/api/v1/stats?period=month
```

## Configuration

### Required Environment Variables
```env
# Kannel Configuration (REQUIRED)
KANNEL_URL=http://your-kannel-server:13013/cgi-bin/sendsms
KANNEL_USERNAME=your_kannel_username
KANNEL_PASSWORD=your_kannel_password  
KANNEL_FROM=+253XXXXXXXX
KANNEL_TIMEOUT=30

# SMS Settings
SMS_DEFAULT_COUNTRY_CODE=+253
SMS_RATE_LIMIT_PER_MINUTE=60
SMS_MAX_LENGTH=160

# Database (Use MySQL in production)
DB_CONNECTION=mysql
DB_DATABASE=apisms
```

### Webhook Configuration  
Configure Kannel to send callbacks:
```
# DLR URL: http://your-app.com/webhooks/kannel/dlr?id=%i&status=%d
# MO URL: http://your-app.com/webhooks/kannel/mo?from=%p&to=%P&text=%b
```

## Development Workflow

### Adding New Features
1. **Models**: Create in `app/Models/` with proper relationships
2. **API**: Add controllers in `app/Http/Controllers/Api/`  
3. **Validation**: Create form requests in `app/Http/Requests/`
4. **Resources**: Format JSON in `app/Http/Resources/`
5. **Services**: Business logic in `app/Services/`

### Testing SMS Integration
1. Use `php artisan sms:test` command for quick validation
2. Check logs with `php artisan pail` for debugging
3. Monitor admin dashboard at `/admin/dashboard` for real-time status
4. Verify webhooks are working by checking delivery reports

## Security Features

- **API Key Authentication**: 32-character secure tokens per client  
- **Rate Limiting**: Configurable per-client limits with Redis
- **IP Restrictions**: Optional IP whitelisting per client
- **Input Validation**: Strict phone number and message validation
- **Request Logging**: All API requests logged with client info
- **Error Handling**: Secure error messages without internal details

## Production Deployment

1. **Environment**: Set production Kannel credentials in `.env`
2. **Database**: Use MySQL/PostgreSQL instead of SQLite
3. **Cache**: Configure Redis for rate limiting and caching  
4. **Queue**: Set up Laravel queue worker for background processing
5. **Monitoring**: Enable application monitoring and log aggregation
6. **SSL**: Ensure HTTPS for API endpoints and webhooks

## Troubleshooting

### Common Issues
- **Kannel Connection**: Test with `php artisan sms:test --check-connectivity`
- **Invalid Phone**: Ensure Djibouti format +253XXXXXXXX  
- **Rate Limits**: Check client rate_limit settings in database
- **Webhooks**: Verify Kannel DLR/MO URLs are configured correctly
- **Authentication**: Confirm API key is active and belongs to client