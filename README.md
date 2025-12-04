### Antes de levantar el proyecto debemos asegurar que tengamos la siguiente lista clara y actualizada de los requisitos para poder levantar (instalar y ejecutar) un proyecto Laravel:

#### * Tener instalado [PHP]([https://pages.github.com/](https://windows.php.net/download). 


# 🖥 Proceso para levantar el proyecto 🖥

## Paso 1
#### Para poder clonar el proyecto se necesita poner el siguiente comando en la consola, se debe acceder a la carpeta donde se quiere clonar el proyecto y ya dentro de la carpeta se abre la terminal y se pone <ins>Git clone (Url del repo)</ins>



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
