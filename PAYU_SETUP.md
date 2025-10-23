# PayU Latam Integration - Setup Guide

## Configuration Status ✅

The PayU Latam integration has been successfully configured for this project.

## Environment Variables

The following PayU configuration is set in `.env`:

```env
# PayU Latam Configuration (Sandbox)
PAYU_MERCHANT_ID=508029
PAYU_ACCOUNT_ID=512321
PAYU_API_KEY=4Vj8eK4rloUd272L48hsrarnUA
PAYU_TEST_MODE=true
PAYU_PAYMENT_URL=https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/
PAYU_RESPONSE_URL=http://localhost:5173/payment-response
PAYU_CONFIRMATION_URL=http://localhost:8000/api/payment/confirmation
```

## API Endpoints

### 1. Create Payment Order (Protected)
- **Endpoint**: `POST /api/payment/create-order`
- **Auth**: Required (JWT)
- **Description**: Creates a payment order and generates PayU form data
- **Request Body**:
```json
{
  "flight_id": "2",
  "seats": [
    {"id": 1},
    {"id": 2}
  ],
  "payer": {
    "full_name": "John Doe",
    "email": "john@example.com",
    "phone": "(123) 456 7890",
    "document_type": "CC",
    "document_number": "12345678"
  },
  "passengers": [
    {
      "full_name": "John Doe",
      "document_type": "CC",
      "document_number": "12345678"
    },
    {
      "full_name": "Jane Doe",
      "document_type": "CC",
      "document_number": "87654321"
    }
  ]
}
```

### 2. Payment Response (Protected)
- **Endpoint**: `GET /api/payment/response`
- **Auth**: Required (JWT)
- **Description**: Receives user redirect from PayU after payment

### 3. Payment Confirmation (Public Webhook)
- **Endpoint**: `POST /api/payment/confirmation`
- **Auth**: Not required (PayU webhook)
- **Description**: Receives payment confirmation from PayU

## Payment Flow

1. **User selects seats** → Frontend stores reservation data
2. **User fills payment form** → Frontend sends data to `/api/payment/create-order`
3. **Backend creates order**:
   - Validates seats availability
   - Creates pending reservations
   - Marks seats as "held" (15 minutes)
   - Generates PayU signature
   - Returns PayU form data
4. **Frontend submits to PayU** → User redirected to PayU payment gateway
5. **User completes payment** → PayU redirects to `PAYU_RESPONSE_URL`
6. **PayU sends confirmation** → Webhook to `PAYU_CONFIRMATION_URL`
7. **Backend processes confirmation**:
   - If approved: Confirms reservations, marks seats as "sold"
   - If rejected: Deletes reservations, releases seats

## Security Features

- ✅ Signature validation on all PayU responses
- ✅ Seat holding mechanism (15 minutes)
- ✅ Duplicate reservation prevention
- ✅ Transaction state validation
- ✅ Cache-based order data storage

## Testing

### Test Cards (Sandbox)
PayU provides test cards for sandbox testing:

**Approved Transaction:**
- Card: 4097440000000004
- CVV: 123
- Expiry: Any future date

**Rejected Transaction:**
- Card: 4097440000000010
- CVV: 123
- Expiry: Any future date

## Production Checklist

Before going to production:

1. [ ] Update `PAYU_TEST_MODE=false`
2. [ ] Replace sandbox credentials with production credentials
3. [ ] Update `PAYU_PAYMENT_URL` to production URL
4. [ ] Update `PAYU_RESPONSE_URL` to production domain
5. [ ] Update `PAYU_CONFIRMATION_URL` to production domain
6. [ ] Configure SSL certificate for webhook endpoint
7. [ ] Test all payment scenarios
8. [ ] Set up monitoring and logging

## Troubleshooting

### 404 Error on Payment
- ✅ **Fixed**: Payment routes added to `routes/api.php`
- Ensure Laravel server is running: `php artisan serve`

### Signature Mismatch
- Verify `PAYU_API_KEY` is correct
- Check amount format (no decimals for COP)
- Ensure reference code matches

### Seats Not Releasing
- Check `hold_expires_at` column exists in `flight_seats` table
- Run migrations: `php artisan migrate`

## Support

For PayU Latam documentation:
- https://developers.payulatam.com/latam/en/docs/getting-started.html