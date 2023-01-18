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

Creo que las pruebas unitarias se pueden explicar solas asi que procedere a mostrar como testeo el endpoint principal de **ZipCode**

<img width="699" alt="Screenshot 2023-01-18 at 14 57 38" src="https://user-images.githubusercontent.com/45053439/213313554-9ebe53b3-4e5c-4ed6-b57b-087c63247733.png">

- Genero primero un factory de **SettlementTypes** para poderlo relacionar con los **Settlements** que creare.
- Uso el factory de **Settlements** para crear 4 y asocialerles el **Settlement Type** generado anteriormente
- Creamos el **Zip Code** y hacemos attach los **Settlements**
- Y hacemos la petición

Aserciones
- Sacar si el *Zip Code* tiene el mismo valor junto con su campo `jurisdiction`
- Ver si los **Settlements** vienen con su relacion **Settlement Type**
- Checar si el JSON cumple con la siguiente estructura
    - zip_code
    - locality
    - federal_entity
    - settlements
    - municipality

Con eso terminamos las pruebas.

### Deployment

Despues de esto subimos el proyecto a este repositorio y al App Platform de Digital Ocean:

<img width="1194" alt="Screenshot 2023-01-18 at 15 00 05" src="https://user-images.githubusercontent.com/45053439/213313922-98e339f3-3cc1-4ea5-ac56-0dd12b4939eb.png">

Con cada commit al master se hace deploy en automatico. (No quise hacer algo mas complicado por temas del tiempo)

### Data Upload

El ultimo reto fue la parte de subida de datos, poder agarrar los Spreadsheets, agruparlos en la nueva estructura de datos y posteriormente subirlos. Asi que hice lo siguiente:    
1. CSV Download
    - Primero baje un CSV a la vez por cada **Federal Entity** y los fui agrupandos en el folder `storage/app/files` poniendoles el nombre de la entidad

2. Artisan Command
    - Despues cree un comando de artisan llamado `upload:settlements` para poder crear una logica que lea el csv y que los vaya asociando
    - Agrupe las columnas en un array para poder hacer mas facil el match
    - Instale el paquete de [Laravel Excel](https://laravel-excel.com/) para poder hacer la leida del CSV lo mas facil posible
    - Cree el comando con 3 parametros: Nombre de la entidad, key y filepath
    - <img width="586" alt="Screenshot 2023-01-18 at 15 07 28" src="https://user-images.githubusercontent.com/45053439/213314873-3e662274-42a6-41bf-a590-fc2a3ee75470.png">
    - Asi podemos correr el siguiente comando `php artisan upload:settlements 'Aguascalientes' 01 app/files/Aguascalientes.csv` para subir todos los **Settlements** de esa **Federal Entity**

2. Handler
    - Primero con los parametros de entidad y key creamos la **Federal Entity** si no existe
    - <img width="706" alt="Screenshot 2023-01-18 at 15 09 23" src="https://user-images.githubusercontent.com/45053439/213315130-92179896-ab10-4522-bf95-019f6e65c3fc.png">
    - Despues leemos el archivo del CSV seleccionado
    - <img width="723" alt="Screenshot 2023-01-18 at 15 09 50" src="https://user-images.githubusercontent.com/45053439/213315184-a13d9483-9b5d-45a6-888a-66efb78e57c0.png">
    - Usamos la funcion de `$this->withProgressBar($slice, function ($row) use ($federal_entity) {` para poder mostrar el progreso de cada registro del csv
    - Mapeamos el nombre de las columnas con cada record en el csv (para poder hacer un uso legible de cada campo y no usar indices)
    - <img width="473" alt="Screenshot 2023-01-18 at 15 10 28" src="https://user-images.githubusercontent.com/45053439/213315277-46a4ab30-995e-44ed-9f71-63985e5c64e3.png">
    - Sacamos la **Municipality** de este **Settlement** y la buscamos o la creamos
    - <img width="665" alt="Screenshot 2023-01-18 at 15 11 09" src="https://user-images.githubusercontent.com/45053439/213315354-52decb88-0154-4a12-a841-4d03f5927aed.png">
    - Buscamos o creamos el **Zip Code** con sus relaciones respectivas a **Municipality** y **Federal Entitty** creadas posteriormente
    - <img width="456" alt="Screenshot 2023-01-18 at 15 11 50" src="https://user-images.githubusercontent.com/45053439/213315440-33ab97b2-0e0a-4c21-bc55-6db0de13c0ee.png">
    - Buscamos o creamos el **Settlement Type**
    - <img width="558" alt="Screenshot 2023-01-18 at 15 12 51" src="https://user-images.githubusercontent.com/45053439/213315669-41097f74-7026-4ad5-ad4a-c5fc6ca1af5e.png">
    - Buscamos o creamos el **Settlement** en cuestion agregando el attach al **Zip Code** y **Settlement Type** creados anteriormente
    - <img width="465" alt="Screenshot 2023-01-18 at 15 13 14" src="https://user-images.githubusercontent.com/45053439/213315729-ed21e1ef-d8ee-45c9-9269-ba75caf854ca.png">
    - Repetimos

3. Bash
    - Para no estar escribiendo un comando a la vez, cree un bash file que tiene todas las **Federal Entities** con su key y archivo respectivo
    - <img width="881" alt="Screenshot 2023-01-18 at 15 14 49" src="https://user-images.githubusercontent.com/45053439/213315934-d6e61a2b-c392-4966-9963-e6df76c91051.png">
    - Nomas lo ejecutamos con `bash upload-settlements.sh`


### Final

Despues de esto nomas quedamos en testear el endpoint en la nube para visualizar los tiempos de respues


<img width="1007" alt="Screenshot 2023-01-18 at 15 16 33" src="https://user-images.githubusercontent.com/45053439/213316183-d95df6a0-e704-490d-90fe-2e5287af10bd.png">
<img width="1057" alt="Screenshot 2023-01-18 at 15 17 24" src="https://user-images.githubusercontent.com/45053439/213316294-8b8b0687-2cf0-4a49-a067-ffabad6d0eb7.png">
<img width="1006" alt="Screenshot 2023-01-18 at 15 17 56" src="https://user-images.githubusercontent.com/45053439/213316353-003ce344-6bb8-4ec4-8817-d19fc40a3012.png">
<img width="1009" alt="Screenshot 2023-01-18 at 15 21 21" src="https://user-images.githubusercontent.com/45053439/213316896-408624c2-4d89-4ef5-a637-d4963eeef48c.png">


> Si se dan cuenta el tiempo de respuesta puede ser un poco mas de 300ms pero eso es parte de la plataforma de Digital Ocean ya que estoy usando el servicio mas basico para poder dar de alta el proyecto, tambien cuenta mucho la concurrencia porque a veces el App se detiene y tarda en responder nueva mente.

**Por eso agregue el campo "laravel_time" dentro de cada request, para que puedan ver el tiempo solo de laravel**

### Conclusion

Sin mas por el momento esto es todo el trabajo realiado, muchas gracias.


### Post Data

No estoy muy de acuerdo que no agregaran los acentos en el ejemplo jajaja

<img width="1303" alt="Screenshot 2023-01-18 at 15 29 17" src="https://user-images.githubusercontent.com/45053439/213317820-4fbabbbe-b1c4-471a-a59f-20fbbb893ab0.png">
