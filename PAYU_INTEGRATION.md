# Integración PayU Latam - Guía de Uso

## 📋 Descripción

Este proyecto integra PayU Latam como pasarela de pagos para procesar las reservas de vuelos. La integración está configurada para funcionar con el **sandbox de pruebas** de PayU.

## 🔑 Credenciales de Sandbox (Ya configuradas)

Las siguientes credenciales están configuradas en el archivo `.env`:

```env
PAYU_MERCHANT_ID=508029
PAYU_ACCOUNT_ID=512321
PAYU_API_KEY=4Vj8eK4rloUd272L48hsrarnUA
PAYU_TEST_MODE=true
PAYU_PAYMENT_URL=https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/
```

## 💳 Tarjetas de Prueba

Para realizar pruebas en el sandbox, usa estas tarjetas:

### ✅ Transacción Aprobada
- **Número:** 4097440000000004
- **CVV:** 123
- **Fecha de expiración:** Cualquier fecha futura
- **Nombre:** APPROVED

### ❌ Transacción Rechazada
- **Número:** 4097440000000010
- **CVV:** 123
- **Fecha de expiración:** Cualquier fecha futura
- **Nombre:** REJECTED

### ⏳ Transacción Pendiente
- **Número:** 4097440000000028
- **CVV:** 123
- **Fecha de expiración:** Cualquier fecha futura
- **Nombre:** PENDING

## 🚀 Flujo de Pago

1. **Selección de Asientos**: El usuario selecciona hasta 5 asientos en la vista `selectSeats.vue`

2. **Formulario de Pago**: El usuario completa sus datos en `pagar.vue`:
   - Datos del pagador (nombre, email, teléfono, documento)
   - Datos del pasajero (nombre, documento)

3. **Creación de Orden**: El backend crea una orden de pago y genera la firma MD5

4. **Redirección a PayU**: El usuario es redirigido a la pasarela de PayU

5. **Procesamiento**: PayU procesa el pago con la tarjeta de prueba

6. **Respuesta**: El usuario regresa a `paymentResponse.vue` con el resultado

7. **Confirmación (Webhook)**: PayU envía una confirmación al backend que:
   - Valida la firma
   - Crea la reserva si el pago fue exitoso
   - Marca los asientos como vendidos
   - O libera los asientos si el pago fue rechazado

## 📡 Endpoints

### Backend (Laravel)

```
POST /api/payment/create-order (Autenticado)
- Crea la orden de pago
- Marca asientos como "held" por 15 minutos
- Retorna datos para enviar a PayU

POST /api/payment/response (Público)
- Recibe la respuesta cuando el usuario regresa de PayU

POST /api/payment/confirmation (Público)
- Webhook de PayU para confirmar el pago
- Crea la reserva y marca asientos como vendidos
```

### Frontend (Vue)

```
/app/selectSeats/:flightId - Selección de asientos
/app/pagar - Formulario de pago
/payment-response - Resultado del pago
```

## 🔒 Seguridad

1. **Firma MD5**: Todas las transacciones incluyen una firma MD5 para validar la integridad
   ```php
   md5("{$apiKey}~{$merchantId}~{$referenceCode}~{$amount}~{$currency}")
   ```

2. **Validación de Firma**: El webhook valida la firma antes de procesar

3. **Asientos Bloqueados**: Los asientos se marcan como "held" durante 15 minutos

4. **Transacciones Atómicas**: La creación de reservas usa transacciones de base de datos

## 🧪 Cómo Probar

1. **Iniciar el backend**:
   ```bash
   cd RetoSenaSoft
   php artisan serve
   ```

2. **Iniciar el frontend**:
   ```bash
   cd FrontendSenaSoft
   npm run dev
   ```

3. **Flujo de prueba**:
   - Inicia sesión en la aplicación
   - Busca un vuelo
   - Selecciona asientos (máximo 5)
   - Completa el formulario de pago
   - Usa una tarjeta de prueba de PayU
   - Verifica el resultado

## 📝 Notas Importantes

- **Modo Sandbox**: La integración está en modo de prueba (`PAYU_TEST_MODE=true`)
- **URLs de Respuesta**: Asegúrate de que las URLs en `.env` coincidan con tu entorno local
- **Webhook Local**: Para probar el webhook en local, necesitas exponer tu servidor con ngrok o similar
- **Logs**: Revisa `storage/logs/laravel.log` para ver los logs de las transacciones

## 🔄 Cambiar a Producción

Para usar PayU en producción:

1. Obtén tus credenciales reales de PayU
2. Actualiza el `.env`:
   ```env
   PAYU_MERCHANT_ID=tu_merchant_id
   PAYU_ACCOUNT_ID=tu_account_id
   PAYU_API_KEY=tu_api_key
   PAYU_TEST_MODE=false
   PAYU_PAYMENT_URL=https://checkout.payulatam.com/ppp-web-gateway-payu/
   PAYU_RESPONSE_URL=https://tu-dominio.com/payment-response
   PAYU_CONFIRMATION_URL=https://tu-dominio.com/api/payment/confirmation
   ```

## 📚 Documentación Oficial

- [PayU Latam - Documentación](https://developers.payulatam.com/)
- [PayU - Integración Webcheckout](https://developers.payulatam.com/latam/es/docs/integrations/webcheckout-integration.html)
- [PayU - Tarjetas de Prueba](https://developers.payulatam.com/latam/es/docs/getting-started/test-your-solution.html)

## 🐛 Troubleshooting

### El pago no se confirma
- Verifica que el webhook URL sea accesible públicamente
- Revisa los logs en `storage/logs/laravel.log`
- Confirma que la firma MD5 sea correcta

### Los asientos no se liberan
- Los asientos "held" expiran automáticamente después de 15 minutos
- Puedes ejecutar un comando para limpiar asientos expirados

### Error de firma inválida
- Verifica que el `PAYU_API_KEY` sea correcto
- Asegúrate de que el formato de la firma coincida con la documentación de PayU