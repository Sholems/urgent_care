# Monime Integration Notes

## Admin Credential Setup

- Open Orchid admin and go to `Configuration -> Monime Credentials`.
- Create one active credential with:
  - `Monime Space ID`
  - `Monime API Token` (stored encrypted at rest)
  - `Monime API Version` (default `caph.2025-08-23`)
  - `Webhook Secret` (optional now, stored encrypted)
- Keep only one active row unless you are intentionally rotating credentials.

## Runtime API Behavior

- Backend uses `MonimeApiService` for all Monime API calls.
- Required outbound headers:
  - `Authorization: Bearer <token>`
  - `Monime-Space-Id: <space_id>`
  - `Monime-Version: <version>`
- Checkout creation uses an `Idempotency-Key` header (UUID per request).
- Payment records persist Monime references:
  - `monime_checkout_session_id`
  - `monime_payment_id` (reserved)
  - `monime_order_number`
  - `provider_payload` (latest response snapshot)

## Webhooks and Reconciliation

- Endpoint is available at `POST /api/webhooks/monime`.
- Current webhook handler is a placeholder for signature/event routing.
- Operationally, checkout completion currently relies on status reconciliation:
  - Mobile returns from WebView
  - App requests checkout status from backend
  - Backend confirms pending/confirmed/failed state

## Rollout Checklist

1. Run migrations in production/staging.
2. Save Monime credentials in admin.
3. Confirm `Monime Credentials` is enabled in admin UI.
4. Validate one checkout each for:
   - request payment
   - specialist payment
   - meal payment
   - appointment card checkout
5. Monitor provider payload snapshots for response shape changes.
6. Add strict webhook signature verification before enforcing webhook-driven state updates.
