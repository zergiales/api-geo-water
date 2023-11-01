# Rutas
> Importacion de las dependencias del aricho public de php

# Rutas y manejo de solicitudes

## `/login`
> Esta ruta maneja las solicitudes POST para iniciar sesion. Verifica que los credenciales proporcionados  en la solicitud con la base de datos y, si son validas, genera, un token JWT (JSON Web token ) para autentificar al usuario. Luego, devuelve, un oobjeto JSON que contiene informacion del usuario y el token.
---
## `/register`
> Esta ruta maneja las solicitudes POST para registrar nuevos usuarios.Realiza una serie de comprobaciones...