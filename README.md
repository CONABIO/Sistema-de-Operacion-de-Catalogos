## 🚀 Empezando

### Estas instrucciones te guiarán para obtener una copia de este proyecto en funcionamiento en tu máquina local con fines de desarrollo, pruebas y revisión del código.
El objetivo es que puedas configurar el entorno necesario, instalar las dependencias del proyecto y ejecutar la aplicación sin complicaciones, siguiendo una serie de pasos ordenados y detallados.


## 📋 Prerrequisitos

- Sistema Operativo: (por ejemplo, Ubuntu 20.04+, Windows 10+, macOS)
- Lenguaje de programación: PHP 8.1+
- Framework: Laravel 10.x / 11.x (según el proyecto)
- Base de datos: MySQL/MariaDB 10+, PostgreSQL 13+, SQLite u otra compatible
- Administrador de dependencias: Composer 2.x
- Sistema Operativo: (por ejemplo, Ubuntu 20.04+, Windows 10+, macOS)
- Gestor de paquetes frontend: NPM 
- Git: para clonar y gestionar el repositorio


# 🖥 Proceso para levantar el proyecto 🖥

## Paso 1
#### Para poder clonar el proyecto se necesita poner el siguiente comando en la consola, se debe acceder a la carpeta donde se quiere clonar el proyecto y ya dentro de la carpeta se abre la terminal y se pone 

´´


## Paso 2

#### Ya clonado el proyecto lo que se debe agregar el .env este archivo de texto plano es utilizado para almacenar variables de entorno, como credenciales de bases de datos, claves de API y otras configuraciones sensibles, separadas del código fuente

## Paso 3

#### Ya agregado el .env lo siguiente es la instalación de dependencias con estos dos comandos

```
composer install
npm install
```

## Paso 4

#### Ya instaladas las dependencias del proyecto lo sigueinte es correr el proyecto con los sigueintes comandos

```
php artisan serve --host=0.0.0.0 --port=8000
npm run dev
```

## Paso 5

##### Como ultimo paso es acceder al navegador en la siguiente URL 

#### http://localhost:8000/
