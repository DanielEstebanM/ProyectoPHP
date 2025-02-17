# README

He implementado las siguientes funcionalidades:

1. **Navegación Mejorada**  
   - Se ha agregado y mejorado la opción de "Siguiente" y "Anterior" en los detalles y en la modificación de clientes.

2. **Validaciones en Operaciones de Nuevo y Modificar**  
   - Verificación de datos al crear o modificar clientes:
     - **Correo Electrónico:** Debe ser válido y no repetido.
     - **Dirección IP:** Debe tener un formato correcto.
     - **Teléfono:** Debe cumplir con el formato `999-999-9999`.

3. **Visualización de Imagen del Cliente**  
   - Se muestra una imagen del cliente almacenada en la carpeta `uploads`.
   - Si no existe una imagen, se genera una imagen aleatoria desde [RoboHash](https://robohash.org/).
   - Las imágenes siguen el formato `00000XXX.jpg`, donde `XXX` es el ID del cliente.

4. **Subida y Cambio de Foto del Cliente**  
   - En las opciones de "Nuevo" y "Modificar", se permite subir o cambiar la foto del cliente.
   - Restricciones:
     - Formatos permitidos: **JPG** y **PNG**.
     - Tamaño máximo: **500 KB**.
     - La imagen no es obligatoria.

5. **Visualización de la Bandera del País**  
   - En la vista de detalles, se muestra la bandera del país asociado a la IP del cliente.
   - Se utilizan los servicios de [ip-api.com](https://ip-api.com/) y [flagpedia.net](https://flagpedia.net/) para obtener esta información.
