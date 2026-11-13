# Trazabilidad de requisitos

Une cada requisito con el módulo que lo cubre, las tablas que lo sostienen y su estado. Se actualiza al cerrar cada fase.

| Requisito | Módulo | Tablas | Fase | Estado |
|---|---|---|---|---|
| Registrar atenciones con folio único | Bitácora | atenciones, folios_atencion | 9 | Primera versión funcional: captura y folio consecutivo |
| Estados y seguimiento de cada atención | Bitácora | estados_atencion, atencion_seguimientos | 9 | Funcional: estado, responsable, comentarios e historial |
| Evidencias de cada atención | Bitácora | atencion_adjuntos | 9 | Funcional: carga y descarga privada de PDF/imágenes |
| Búsqueda y filtros | Bitácora | Índices en atenciones | 9 y 12 | Funcional: folio, solicitante, matrícula, asunto, estado y fechas |
| Catálogos (servicios, departamentos, tipos, periodos, carreras) | Catálogos | servicios, departamentos, tipos_atencion, tipos_problema, estados_atencion, periodos_escolares, carreras | 9 | Funcional: alta y activación/desactivación |
| Personal, firmantes y destinatarios | Catálogos | personal, personal_rol, roles_documento | 9 y 10 | Funcional: alta de personal y asignación de cargos de firma |
| Usuarios, roles y permisos | Seguridad | users, roles, permissions (Spatie) | 8 | Funcional: alta, edición, cambio de rol, contraseña y desactivación |
| Auditoría de acciones | Seguridad | activity_log | 13 | Funcional: registro de acciones principales y vista de consulta |
| Estudiantes (datos permanentes) | Estudiantes | estudiantes | 9 | Funcional: alta, consulta, edición y búsqueda; contacto por permiso |
| Servicio Social | Trámites | tramites_servicio_social | 9 | Funcional: registro, consulta y cambio de estado |
| Residencias Profesionales | Trámites | tramites_residencia | 9 | Funcional: registro, consulta y cambio de estado |
| Créditos / actividades complementarias | Trámites | tramites_actividad_complementaria | 9 | Funcional: registro y consulta |
| Seguimiento de estudiantes para el jefe de carrera | Seguimiento | estudiantes + trámites en curso (sin tabla nueva) | 9 | Funcional: panel de residencias y servicio social activos |
| Generar los 5 documentos Word con folio y firmantes | Documentos | tipos_documento, bloques_firma, folios_contador, documentos_generados, documento_firmante | 10 | Funcional: DOCX, folio consecutivo y firmantes archivados; requiere ajustar a las plantillas oficiales |
| Conservar archivo generado y su huella | Documentos | documentos_generados | 10 | Funcional: almacenamiento privado y verificación SHA-256 al descargar |
| Dashboard | Dashboard | Consultas sobre atenciones y documentos | 12 | Funcional: métricas del día, alumnos/docentes, pendientes, resueltas y últimas atenciones |
| Seguridad (sesiones, validaciones, protección de archivos) | Seguridad | — | 8 y 13 | Parcial: middleware, autorización por permiso, validación, límite de intentos y archivos privados; falta hardening de producción |
| Pruebas | Calidad | tests/ | Todas | Funcional: pruebas de acceso, bitácora, estudiantes, trámites, documentos, catálogos y usuarios |
| Manuales técnico y de usuario, respaldo y restauración | Documentación | docs/ | Todas | En curso |
| Entradas y salidas del Área de Sistemas; cuántos alumnos y docentes llegan y se atienden por día | Bitácora y Dashboard | atenciones (fecha_hora, hora_salida, tipo_solicitante, estado_id) | 9 y 12 | Registro de fecha/hora y hora de salida; panel diario por tipo de solicitante |
