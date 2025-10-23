<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets de Vuelo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        
        .header {
            background: #e60000;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 14px;
        }
        
        .summary {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .summary-row strong {
            color: #e60000;
        }
        
        .ticket {
            border: 2px solid #e60000;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            page-break-inside: avoid;
            background: white;
        }
        
        .ticket-header {
            background: #e60000;
            color: white;
            padding: 10px;
            margin: -20px -20px 15px -20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        
        .ticket-header h2 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .ticket-code {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        
        .ticket-body {
            display: table;
            width: 100%;
        }
        
        .ticket-section {
            margin-bottom: 15px;
        }
        
        .ticket-section h3 {
            color: #e60000;
            font-size: 14px;
            margin-bottom: 8px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            padding: 5px 0;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
        }
        
        .info-value {
            color: #333;
        }
        
        .flight-route {
            text-align: center;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            margin: 15px 0;
        }
        
        .flight-route .route {
            font-size: 20px;
            font-weight: bold;
            color: #e60000;
            margin-bottom: 5px;
        }
        
        .flight-route .arrow {
            font-size: 24px;
            color: #666;
        }
        
        .seat-info {
            background: #fff3cd;
            border: 2px dashed #ffc107;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            margin: 15px 0;
        }
        
        .seat-info .seat-number {
            font-size: 32px;
            font-weight: bold;
            color: #e60000;
            margin-bottom: 5px;
        }
        
        .seat-info .seat-label {
            font-size: 12px;
            color: #666;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            color: #666;
            font-size: 10px;
        }
        
        .barcode {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background: white;
            border: 1px solid #ddd;
        }
        
        .barcode-lines {
            height: 60px;
            background: repeating-linear-gradient(
                90deg,
                #000 0px,
                #000 2px,
                #fff 2px,
                #fff 4px
            );
            margin-bottom: 5px;
        }
        
        @media print {
            .ticket {
                page-break-after: always;
            }
            
            .ticket:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🛫 CONDOR AIRLINES</h1>
        <p>Pases de Abordar</p>
    </div>
    
    <div class="summary">
        <div class="summary-row">
            <span><strong>Código de Referencia:</strong></span>
            <span>{{ $reference_code }}</span>
        </div>
        <div class="summary-row">
            <span><strong>Fecha de Emisión:</strong></span>
            <span>{{ $issue_date }}</span>
        </div>
        <div class="summary-row">
            <span><strong>Total de Tickets:</strong></span>
            <span>{{ count($tickets) }}</span>
        </div>
        <div class="summary-row">
            <span><strong>Monto Total:</strong></span>
            <span><strong>${{ number_format($total_amount, 0, ',', '.') }} COP</strong></span>
        </div>
    </div>
    
    @foreach($tickets as $ticket)
    <div class="ticket">
        <div class="ticket-header">
            <h2>PASE DE ABORDAR</h2>
            <div class="ticket-code">{{ $ticket['reservation_code'] }}</div>
        </div>
        
        <div class="ticket-body">
            <!-- Información del Pasajero -->
            <div class="ticket-section">
                <h3>👤 INFORMACIÓN DEL PASAJERO</h3>
                <div class="info-row">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value">{{ strtoupper($ticket['passenger_name']) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Documento:</span>
                    <span class="info-value">{{ $ticket['passenger_document'] }}</span>
                </div>
            </div>
            
            <!-- Ruta del Vuelo -->
            <div class="flight-route">
                <div class="route">
                    {{ $ticket['origin'] }} <span class="arrow">✈</span> {{ $ticket['destination'] }}
                </div>
                <div style="font-size: 12px; color: #666;">Vuelo #{{ $ticket['flight_id'] }}</div>
            </div>
            
            <!-- Información del Vuelo -->
            <div class="ticket-section">
                <h3>✈️ DETALLES DEL VUELO</h3>
                <div class="info-row">
                    <span class="info-label">Fecha:</span>
                    <span class="info-value">{{ $ticket['departure_date'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Hora de Salida:</span>
                    <span class="info-value">{{ $ticket['departure_time'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Aeronave:</span>
                    <span class="info-value">{{ $ticket['airplane'] }}</span>
                </div>
            </div>
            
            <!-- Información del Asiento -->
            <div class="seat-info">
                <div class="seat-number">{{ $ticket['seat_code'] }}</div>
                <div class="seat-label">ASIENTO ASIGNADO</div>
                <div style="margin-top: 5px; font-size: 11px; color: #666;">
                    Clase: {{ ucfirst($ticket['seat_class']) }}
                </div>
            </div>
            
            <!-- Información de Pago -->
            <div class="ticket-section">
                <h3>💳 INFORMACIÓN DE PAGO</h3>
                <div class="info-row">
                    <span class="info-label">Pagador:</span>
                    <span class="info-value">{{ $ticket['payer_name'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $ticket['payer_email'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Precio:</span>
                    <span class="info-value"><strong>${{ $ticket['price'] }} COP</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Estado:</span>
                    <span class="info-value">
                        <span class="status-badge status-{{ $ticket['status'] }}">
                            {{ strtoupper($ticket['status']) }}
                        </span>
                    </span>
                </div>
            </div>
            
            <!-- Código de Barras Simulado -->
            <div class="barcode">
                <div class="barcode-lines"></div>
                <div style="font-size: 10px; letter-spacing: 3px;">{{ $ticket['reservation_code'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
    
    <div class="footer">
        <p><strong>CONDOR AIRLINES</strong></p>
        <p>Por favor, llegue al aeropuerto con 2 horas de anticipación</p>
        <p>Presente este pase de abordar y su documento de identidad en el mostrador de check-in</p>
        <p style="margin-top: 10px;">© {{ date('Y') }} Condor Airlines. Todos los derechos reservados.</p>
    </div>
</body>
</html>