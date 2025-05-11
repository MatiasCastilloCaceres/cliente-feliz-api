# Cliente Feliz API

Este proyecto es una API para gestionar clientes, pedidos y productos en la base de datos "cliente_feliz". A continuación se detallan las instrucciones de instalación, uso y detalles sobre la API.

## Instalación

1. Clona el repositorio en tu máquina local:
   ```
   git clone <URL_DEL_REPOSITORIO>
   ```

2. Navega al directorio del proyecto:
   ```
   cd cliente-feliz-api
   ```

3. Configura la conexión a la base de datos editando el archivo `config/database.php` con tus credenciales de base de datos.

4. Asegúrate de tener un servidor web (como Apache) configurado para servir el proyecto. Puedes usar un entorno local como XAMPP o MAMP.

## Uso

1. Inicia el servidor web y accede a `http://localhost/cliente-feliz-api/index.php` para interactuar con la API.

2. La API soporta los siguientes métodos HTTP:
   - **GET**: Para obtener información.
   - **POST**: Para crear nuevos registros.
   - **PUT**: Para actualizar registros existentes.
   - **DELETE**: Para eliminar registros.

## Endpoints

### Clientes
- `GET /clientes`: Obtiene la lista de todos los clientes.
- `POST /clientes`: Crea un nuevo cliente.
- `GET /clientes/{id}`: Obtiene un cliente específico por ID.
- `PUT /clientes/{id}`: Actualiza un cliente específico por ID.
- `DELETE /clientes/{id}`: Elimina un cliente específico por ID.

### Pedidos
- `GET /pedidos`: Obtiene la lista de todos los pedidos.
- `POST /pedidos`: Crea un nuevo pedido.
- `GET /pedidos/{id}`: Obtiene un pedido específico por ID.
- `PUT /pedidos/{id}`: Actualiza un pedido específico por ID.
- `DELETE /pedidos/{id}`: Elimina un pedido específico por ID.

### Productos
- `GET /productos`: Obtiene la lista de todos los productos.
- `POST /productos`: Crea un nuevo producto.
- `GET /productos/{id}`: Obtiene un producto específico por ID.
- `PUT /productos/{id}`: Actualiza un producto específico por ID.
- `DELETE /productos/{id}`: Elimina un producto específico por ID.

## Contribuciones

Las contribuciones son bienvenidas. Si deseas contribuir, por favor abre un issue o envía un pull request.

## Licencia

Matias Castillo# cliente-feliz-api
# cliente-feliz-api
# cliente-feliz-api
