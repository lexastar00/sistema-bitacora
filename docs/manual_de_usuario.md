# Manual de usuario

## Acceso

1. Inicia el servidor Laravel y abre `http://127.0.0.1:8000/login`.
2. Ingresa el correo y la contraseña que te proporcionó el administrador.
3. Usa **Salir** para cerrar la sesión. Las cuentas desactivadas no pueden iniciar sesión.

El menú muestra solo los módulos permitidos para tu rol. No compartas tu contraseña. El administrador debe cambiar la contraseña inicial antes de cualquier uso compartido.

## Bitácora de atenciones

- En **Atenciones**, busca por folio, nombre, matrícula o asunto. También puedes filtrar por estado y rango de fechas.
- En **Registrar atención**, completa solicitante, asunto, servicio, tipo de atención, responsables y descripción. El sistema asigna el folio.
- Abre **Ver detalle** para revisar el registro, añadir comentarios, cambiar estado o responsable si tu rol lo permite y consultar el historial.
- Al marcar una atención como resuelta o cancelada se registra la hora de salida; al reabrirla se limpia esa hora.
- Para agregar evidencia usa PDF o imagen JPG/PNG de hasta 10 MB. Los archivos se guardan en almacenamiento privado y se descargan desde el detalle de la atención.

## Estudiantes y seguimiento

- En **Estudiantes**, busca por matrícula o nombre y registra estudiantes con carrera, periodo y estatus.
- En la ficha de cada estudiante registra trámites de Residencia Profesional, Servicio Social o Actividades Complementarias.
- El estado de Residencia y Servicio Social puede actualizarse desde la ficha.
- **Seguimiento** reúne los trámites de Residencia y Servicio Social en curso.
- El correo y teléfono se muestran solo a los roles autorizados.

## Documentos

1. Registra el estudiante y el trámite correspondiente.
2. En **Personal**, agrega las personas firmantes y marca sus cargos oficiales.
3. En **Documentos → Generar documento**, selecciona tipo, estudiante, trámite, destinatario y firmantes.
4. Descarga el archivo desde el historial y revisa que folio, datos y firmantes sean correctos.

Los DOCX generados son borradores de contenido. Antes de emitirlos como documentos oficiales, aplica y valida el formato y texto institucional vigente.

## Administración

- **Usuarios:** crea cuentas, asigna uno de los roles predefinidos, cambia contraseña o desactiva el acceso.
- **Personal:** registra responsables y asigna los cargos usados en documentos.
- **Catálogos:** agrega o activa/desactiva carreras, departamentos, servicios, tipos de atención, tipos de problema y periodos.
- **Auditoría:** consulta las acciones principales realizadas en el sistema.

Si un módulo o botón no aparece, solicita al administrador que revise el rol de tu cuenta.
