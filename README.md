# 📌 Sistema para la Operación de los Catálogos de Autoridades Taxonómicas

## 🧠 Descripción

#### El Sistema para la Operación de los Catálogos de Autoridades Taxonómicas (SOCAT), fue desarrollado por la SubCoordinación de Informática para ayudar en la captura, actualización y depuración de los Catálogos de Autoridades Taxonómicas que tiene a cargo la SubCoordinación de Autoridades Taxonómicas (SCAT) de la CONABIO y que es parte fundamental del Sistema Nacional de Información sobre Biodiversidad (SNIB).


## 🚀 Empezando

### Estas instrucciones te guiarán para obtener una copia de este proyecto en funcionamiento en tu máquina local con fines de desarrollo, pruebas y revisión del código.
### El objetivo es que puedas configurar el entorno necesario, instalar las dependencias del proyecto y ejecutar la aplicación sin complicaciones, siguiendo una serie de pasos ordenados y detallados.





## 📋 Prerrequisitos

- Sistema Operativo: Windows 10+
- Lenguaje de programación: PHP 8.1+
- Framework: Laravel 11.x 
- Base de datos: MySQL/MariaDB 
- Administrador de dependencias: Composer 2.x
- Gestor de paquetes frontend: NPM 
- Git: para clonar y gestionar el repositorio


##  🔧 Instalación y levntamiento

## Paso 1
#### Para poder clonar el proyecto se necesita poner el siguiente comando en la consola, se debe acceder a la carpeta donde se quiere clonar el proyecto y ya dentro de la carpeta se abre la terminal y se pone 

```
git clone (URL del repositorio)
```


## Paso 2

#### Ya clonado el proyecto lo que se debe agregar el .env este archivo de texto plano es utilizado para almacenar variables de entorno, como credenciales de bases de datos, claves de API y otras configuraciones sensibles, #### separadas del código fuente, debes generar tu propio archivo .env a partir de este archivo de ejemplo: 

```
cp .env.example .env
```

#### Copiado el archivo puedes empezar a modificar las variables y conexiones a tu base de datos 


## Paso 3

#### Ya agregado el .env lo siguiente es la instalación de dependencias con estos dos comandos

```
composer install
npm install
```


## Paso 4

##### Antes de correr el proyecto es necesario modificar el vite.config.js 

```
server: {
        host: ',  
        port: ,       
        strictPort: true, 
      },
```

#### Se debe modificar el host y poner la ip del equipo donde se va a correr el proyecto y el puerto 

## Paso 5

#### Ya instaladas las dependencias del proyecto y configurado el vite.config.js lo sigueinte es correr el proyecto con los sigueintes comandos

```
php artisan serve --host=0.0.0.0 --port=8000
npm run dev
```

## Paso 6

##### Como ultimo paso es acceder al navegador en la siguiente URL 

#### http://localhost:8000/
