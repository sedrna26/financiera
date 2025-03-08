# Sistema de Gestión Financiera

## Descripción
Este sistema está diseñado para gestionar clientes y créditos de una financiera privada, permitiendo un control automatizado de los estados de los clientes, generación de documentos legales y seguimiento de pagos.

## Características Principales
### Gestión de Clientes
- Almacenar todos los clientes de la financiera.
- Listar y buscar clientes.
- Clasificación automática de clientes en:
  - **Clientes Activos**: Tienen créditos en curso y están realizando pagos.
  - **Clientes Deudores**: Tienen más de dos meses sin abonar una cuota de alguno de sus créditos.
  - **Clientes Inactivos**: Cancelaron sus créditos y pueden volver a ser activos si se les otorga un nuevo crédito.
  - **Clientes en Recuperación**: Clientes que comenzaron a regularizar su deuda.
- Registro de datos del cliente:
  - Número de Cliente
  - Nombre y Apellido
  - DNI
  - Domicilio
  - Teléfono
  - Estado actual

### Automatización de Estados
- El sistema cambia automáticamente el estado del cliente:
  - De **Activo** a **Deudor** si tiene dos cuotas impagas.
  - De **Deudor** a **En Recuperación** si comienza a pagar su deuda.
  - De **En Recuperación** a **Activo** si se regularizan todas las cuotas.

### Generación de Documentos
- Generación de **Pagaré** y **Contrato de Mutuo**.
- Documentos listos para impresión.

### Mainboard del Sistema
- Fecha actual.
- Lista de clientes que deben abonar el día de la fecha.
- Lista de clientes con pagos programados en la semana.
- Simulador de cuotas.

### Simulador de Cuotas
- Datos de entrada:
  - Monto a prestar.
  - Cantidad de cuotas.
  - Frecuencia de pago (semanal, quincenal o mensual).
- Cálculo automático de montos de cuotas.
- Visualización del cronograma de pagos.

### Creación de Créditos
1. Selección de cliente desde la lista o mediante búsqueda.
2. Si el cliente no existe, se redirige a la página de registro.
3. Carga de datos del crédito.
4. Generación de pagaré y contrato de mutuo.
5. Registro automático del crédito y actualización del estado del cliente.

### Control de Pagos
- Registro de pagos y generación de recibos.
- Alertas de vencimientos de cuotas.
- Historial de pagos por cliente.

### Roles y Permisos
- Administración de acceso según tipo de usuario:
  - Administrador.
  - Gestor de cobranzas.
  - Supervisor.
- Registro de auditoría de cambios.

### Reportes y Estadísticas
- Total de dinero prestado vs. dinero recuperado.
- Listado de clientes con mejor historial de pagos.
- Créditos otorgados en los últimos meses.
- Porcentaje de morosidad.

### Exportación de Datos
- Exportación de información en formatos Excel y CSV.

### Opción de Refinanciación
- Posibilidad de renegociar una deuda y generar un nuevo plan de pagos.

