# Backbone Code Challenge - Laravel Backend Developer
En este proyecto podrán acceder al repositorio para el API de Códigos Postales de México como parte del reto de Backbone Systems aplica.

## Analisis

Lo primero que intentamos resolver es analizando la fuente de datos proporcionada ([Códigos Postales](https://www.correosdemexico.gob.mx/SSLServicios/ConsultaCP/CodigoPostal_Exportar.aspx)) al parecer nos la proporcionan en 3 diferentes formatos:

![image](https://user-images.githubusercontent.com/45053439/213289794-cd4571c4-0211-488c-b549-4727fa0bfacb.png)


Al revisar la fuente de Excel en Google Sheet se pudieron observar como los datos estaban organizados


<img width="300" alt="Screenshot 2023-01-18 at 12 40 27" src="https://user-images.githubusercontent.com/45053439/213289883-d6177ad0-18cd-43bd-a07a-f848d523db9b.png">


Lo cual me llevo a realizar una comparativa con los datos que mostraban en el [API de ejemplo](https://jobs.backbonesystems.io/api/zip-codes/01210) para hacer match los campos con la respuesta del mismo. Lo cual notas en seguida que el [API de ejemplo](https://jobs.backbonesystems.io/api/zip-codes/01210) regresa campos mas organizados, relacionados y en ingles. Asi que empece a intentar replicar la estructura de Base de Datos.

## Base de Datos

En pgAdmin use el tool de **ERD Diagram** que es un canvas para hacer Diagramas Entidad Relación, en seguida fui declarando las tablas conforme a la comparativa realizada con el [API de ejemplo](https://jobs.backbonesystems.io/api/zip-codes/01210) sacando las siguientes tablas:

- Federal Entity
- Municipalities
- Zip Codes
- Settlements
- Selttement Types

Fui desmenusando las tablas con relaciones simples como las de Federal y Municipality, luego ya trabaje con la relacion entre Zip Code y Settlements (la cual note era de muchos a muchos) y seguido de agregar una relacion mas para Settlements con sus Types.

![image](https://user-images.githubusercontent.com/45053439/213291435-81169c5c-1778-4943-8d21-2273be5d99c9.png)

    > Como extra agregue los timestamps y los deleted_at para el soft delete aunque se que no le dare un uso por el momento.

## Laravel

Una vez revisando que la estructura de mi BD estuviera correcta, procedi a crear el proyecto de Laravel borrando todas las rutas innecesarias como **web** y **channel**, desinstale el Laravel Sanctum que viene por default para que no me cree migraciones innecesarias y procedi a instalar mi ambiente usando **sail**

<img width="500" alt="Screenshot 2023-01-18 at 12 52 43" src="https://user-images.githubusercontent.com/45053439/213292150-78c1c3eb-c063-49c7-886e-23c76a89bf2a.png">

### Migraciones

Las migraciones fueron muy sencillas, simplemente segui un orden para no generar conflictos con las llaves foraneas agregando primero **Federal Entities** y **Municipalities**, seguido de **Settlements Types** y **Zip Codes**, dejando por ultimo **Settlements** con el pivote **Settlement Zip Code**

<img width="529" alt="Screenshot 2023-01-18 at 12 55 28" src="https://user-images.githubusercontent.com/45053439/213292634-502a235c-31e3-445b-b206-cf5a83891813.png">

    > Tal vez las tablas que mas importen son dos, la de Zip Codes y la de Settlement Zip Codes, asi que describire un poco como cree sus migraciones.

En la tabla de **Zip Codes** cree un indice a la columna `zip_code` para agilizar su busqueda en el request y seguido de dos llaves foraneas utilizadas para sus relaciones uno a muchos con **Federal Entities** y **Municipalities**.

<img width="604" alt="Screenshot 2023-01-18 at 12 59 26" src="https://user-images.githubusercontent.com/45053439/213293358-171de83e-ae50-4a52-812b-2d7e73df4cb9.png">

En **Settlement Zip Code** simplemente se asignan las dos foraneas tanto de **Zip Codes** como de **Settlements**.

<img width="666" alt="Screenshot 2023-01-18 at 13 02 42" src="https://user-images.githubusercontent.com/45053439/213293954-1980541d-378a-4306-bb4f-f283ec1408fc.png">

### Models

Los modelos no tuvieron mucha ciencia despues de haber creado las migraciones, simplemente use el comando de laravel que me genera el Modelo, Factory y Seeder:

    > php artisan make:model ZipCode -mfsc
    // Esto me genera incluso el controlador, pero nomas los borre dejando solo el de ZipCodeController
    
En seguida procedi a agregar las relaciones correspondientes, igual sin mucho problema pero mostrare principalmente la de ZipCode ya que es la que tiene mas:

<img width="698" alt="Screenshot 2023-01-18 at 13 07 22" src="https://user-images.githubusercontent.com/45053439/213294837-99757b31-aa44-4fa9-ac3c-15193a97fcef.png">

### Factories y Seeders

En seguida empece a trabajar con los **Factories** para empezar a popular una base de datos de prueba y testing, tambien nomas siguiendo la misma dinamica de como se usan los factories:

<img width="621" alt="Screenshot 2023-01-18 at 13 08 42" src="https://user-images.githubusercontent.com/45053439/213295089-fca53c82-4519-4045-915d-160b92bf2627.png">

Los seeders fueron los que tuvieron un poco mas de ciencia para poder relacionar todo en orden y a la vez generar multiples records en la BD con el fin de testear la velocidad de consulta, se genero un seeder global donde se crean los registros necesarios:

<img width="915" alt="Screenshot 2023-01-18 at 13 10 03" src="https://user-images.githubusercontent.com/45053439/213295340-097e680c-3c4a-441d-8e63-9725ba9fea25.png">

Generamos
- 31 Federal Entities
- 100 Municipalities
- 15 Settlement Types
- Por cada Federal Entity:
    - Creamos de 1 a 99 Settlements
    - 200 Zip Codes
    
En la imagen anterior se muestra como se asocian todas las cosas usando `for` y `hasAttached`.

### Rutas

Cree dos rutas (aunque se que solo se usa una) para nomas hacer pruebas de una paginación y un get normal para un solo Zip Code, las rutas se organizaron de la siguiente manera:


<img width="743" alt="Screenshot 2023-01-18 at 13 13 27" src="https://user-images.githubusercontent.com/45053439/213295963-0e7a9b1d-ace3-40cc-ab55-628151c35c7f.png">

    > Siempre pongo una ruta de live por costumbre de usar Kubernetes

Agrupo al controlador ZipCodeController, les pongo un name zip-codes. (para usarlo despues en las pruebas) y genero las rutas de **index** y el **show* respectivamente.

### Controlador

Aqui nomas uso las dos funciones de **index** y **show*, donde en la primera hago una paginacion basica y en la segundo uso el **Route Binding** para hacer la consulta.

<img width="654" alt="Screenshot 2023-01-18 at 13 16 43" src="https://user-images.githubusercontent.com/45053439/213296558-a6b06db1-cd96-4d18-bcfc-812c3fc1fe09.png">

Creo que aqui no hay nada de otro mundo.

### Resource

Dentro de estos archivos es donde empiezo a declarar las relaciones para mandar en mis respuestas, hice un resource de cada entidad para poder formatear de la mejor manera el endpoint.

<img width="259" alt="Screenshot 2023-01-18 at 13 17 46" src="https://user-images.githubusercontent.com/45053439/213296758-2357420e-de45-4950-8398-08d785223ce2.png">

**Federal Entity**, **Municipality** y **Settlement Type** no tienen nada y estan como default con un:
> public function toArray($request) { return parent::toArray($request); }

Pero ya en **Zip Codes** y **Settlements** empiezo a agregar sus relaciones.

<img width="891" alt="Screenshot 2023-01-18 at 13 20 22" src="https://user-images.githubusercontent.com/45053439/213297313-472a5315-5f6e-4dcb-8309-2f1394874558.png">

En Zip Codes agrego las 3 relaciones importantes:
- Municipality
- Federal Entity
- Settlements
Esta ultima siendo una collection ya que es una relacion de muchos a muchos.

<img width="891" alt="Screenshot 2023-01-18 at 13 20 35" src="https://user-images.githubusercontent.com/45053439/213297354-653eb341-605f-45f8-8da0-6c6ec0a0dea5.png">

En Settlements nomas agrego la relacion a:
- Settlement Type

Para poder limpiar un poco los responses agregue el campo `visible` a los modelos para declarar cuales campos estaran disponibles en la serializacion:

<img width="597" alt="Screenshot 2023-01-18 at 13 22 44" src="https://user-images.githubusercontent.com/45053439/213297744-6fa429d7-7cfe-46b2-8231-62cd43583968.png">

Aun asi los responses que generan no vienen cargados con las relaciones debido a que no estoy ejecutando en ningun lado el **Eager Loading o el Lazy Loading** por lo que para simplificarme un poco las cosas sobre escribir un poco el Route Binding del modelo Zip Code para lograr esto.

<img width="987" alt="Screenshot 2023-01-18 at 13 25 01" src="https://user-images.githubusercontent.com/45053439/213298208-255bae35-0820-4087-8190-a596ef110bb8.png">

> Se que pude haber hecho esto en el controlador pero se me hizo una forma mas sencilla para este ejemplo, si despues agrego mas rutas como delete, store o update creo que si tendria que quitarlo y hacerlo uso exclusivo de algunas rutas en particular

Con esto las relaciones son cargadas con **Eager Loading** para optimizar un poco la consulta y ya son mostradas en el request.

<img width="423" alt="Screenshot 2023-01-18 at 13 27 45" src="https://user-images.githubusercontent.com/45053439/213298714-9dca225f-b006-4d0d-8394-17410ffe0ce6.png">

**Pero** aun asi no me muestra el ultimo nivel de relacion de los **Settlements**... el **Settlement Type**, para eso solo agregue el campo `$with` dentro del modelo **Settlement**:

<img width="510" alt="Screenshot 2023-01-18 at 13 28 55" src="https://user-images.githubusercontent.com/45053439/213298923-cc013a4d-b58b-4db5-b0bc-e195e717b421.png">

Y listo, con eso tenemos la consulta terminada:

<img width="354" alt="Screenshot 2023-01-18 at 13 29 48" src="https://user-images.githubusercontent.com/45053439/213299072-61ab7f52-24a1-4f8e-8fd7-a6d3b63f4eb5.png">

### Tiempos de Respuesta

Parte del objetivo del curso es poder optimizar el codigo y la estructura de una manera que los responses no tarden mas de 300ms, por eso utilice **Eager Loading**, los indices y las foraneas.

Pero me pasaba algo muy interesante en mi computador, las respuestas tardaban mas de 4 segundos lo cual me hizo sospechar un poco de **Laravel Sail** porque al hacer una prueba del tiempo de respuesta de laravel me di cuenta que si era por debajo de los 300ms, asi que puse un timer en mi Api Resource (creo que lo notaron en una anterior fotografia del ZipCodesResource)
> 'laravel_time' => intval((microtime(true) - LARAVEL_START) * 1000) . 'ms',

Con este calculo puedo sacar el tiempo que le toma laravel para serializar la respuesta, el LARAVEL_START se genera en la primera linea de codigo del `public/index.php`:

<img width="456" alt="Screenshot 2023-01-18 at 13 34 36" src="https://user-images.githubusercontent.com/45053439/213299897-1f222d7c-10ea-472a-a906-cc79c88c33c9.png">

Entonces pude suponer que el Docker me estaba demorando en cargar, despues de unos ajustes en la PC con la que estaba trabajando pude darme cuenta que efectivamente el Docker demoraba, al realizar unos cambios al WSL y la manera en la que se generan los contenedores en Windows usando un Distro de Ubuntu pude hacer con exito una ejecución rapida.
- Entre 20 y 80ms en mi PC
- Y 120 a 150ms en la Macbook

### Testing

Despues de ya tener mi app empece con los testing Unitarios y de Features
- Los unitarios los uso para testear creaciones, relaciones y conteos
- Los de feature los uso para hacer consultas a los routes como si un cliente lo realizara

<img width="260" alt="Screenshot 2023-01-18 at 13 39 54" src="https://user-images.githubusercontent.com/45053439/213300800-64998fcd-7c3f-4dea-a280-454975774db8.png">

Creo que las pruebas unitarias se pueden explicar solas asi que procedere a mostrar como testeo el endpoint principal de ZipCode

<img width="593" alt="Screenshot 2023-01-18 at 13 41 01" src="https://user-images.githubusercontent.com/45053439/213301020-f9c11770-f85d-4921-9230-7f24d48c3091.png">

- Genero primero un factory de ZipCode que vendra con su relacion de Federal y Municipality por default gracias a su factory

