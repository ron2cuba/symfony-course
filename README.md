# Symfony Course

## la mise en place

- mise en place du projet par la CLI Symfony:
```sh
symfony new offoron --version=5.1
```
- Installer le https :
```sh
symfony server:ca:install
```
Lancer le projet en daemon:
```sh
symfony serve -d
```
Pour le stopper :
```sh
symfony server:stop
```

## Les routes

Elles sont configurées dans le dossier `config/routes/routes.yaml`.
<!-- décomenter le code existant -->
En  analysant on trouve logique :
```yaml
index:
   path: /
   controller: App\Controller\DefaultController::index
```
Donc route = identifiant un path et un controller avec la class::methode

Se faire une route et un controller de test.

## HttpFoundation: Requête et Réponse

Dans un controller, plutôt que d'utiliser les super globales `$_GET` ou autres, préférer se servir de l'objet `Request` :

```php
public function test()
    {
        $request = Request::createFromGlobals();
        
        $age = $request->query->get('age', 0);

        return new Response("vous avez $age ans");
    }
```

plutôt que l'écriture normale:
```php
public function test()
    {
        // donner une valeur par défaut pour éviter l'erreur
        $age = 0;

        if(!empty($_GET['age'])){
            $age = $_GET['age'];
        }

        dd("vous avez $age ans");
    }
```
Si on `dump($request);` dans le controller on observe tous les "Bags" (<u>tableaux associatifs</u>) que revoie cet Objet. Un bag possède des "methodes" supplémentaires qui vont permettre de travailler avec ces tableaux. 

- `query` représente les paramètres passés dans l'url en GET.

- `request` représente les parametres en POST.

- `attributes` représente des infos supplémentaires que Symfony va mettre dans la requête.

/!\ <b><u>Le controller renvoie toujours une instance de la classe Response du pâckage HttpFoundation</u></b> /!\

<b>Retenir :

Pour analyser la requête Http on utilisera l'objet <u>`Request`</u> avec ses methodes et tous nos controller doivent retourner un objet de la classe <u>`Response`</u></b>

# Routes parametrables

le système de routes est géré par le package `Symfony/Routing`.

Simplification des urls en:
`test/age/33` plutot que `test?age=33`

Création de routes avec un paramètre de route en utilisant les accolades `{age}` dans `routes.yaml`
```yaml
test:
  path: /test/{age}
  controller: App\Controller\TestController::test
  ```
  Quand on `dump($request)` on peut retrouver cette info dans les bags `attributes`.

  Symfony ajoute le nom de la route qui est utilisée, le controller qui est appelé et le parametre de route.

  Donc dans le Controller `App\Controller\TestController::test` :
  ```php
  class TestController
{
    public function index()
    {
        dd("test");
    }
    
    public function test(Request $request)
    {
        // utilisation du bag parameters
        dump($request);
        
        // $age = $request->query->get('age', 0);

        $age =  $request->attributes->get('age');

        return new Response("vous avez $age ans");
    }
}
```
On retrouve les paramètres de la route dans les attrbuts de la request, il faut mettre aussi une valeur par défaut dans la route pour éviter l'erreur + des requirements, par une regex, pour éviter les incohérences :
```yaml
test:
  path: /test/{age}
  controller: App\Controller\TestController::test
  defaults:
    age: 0
  requirements:  
    age: \d+
```
On peut réécrire les paramètres par défaut directement dans le path comme les requirements entre chevrons:
```yaml
test:
  path: /test/{age<\d+>?0}
  controller: App\Controller\TestController::test
```
Il est encore possible de simplifier l'écriture en placant l'âge en paramètre de la fonction :

Le controller devient :
```php
public function test(Request $request, $age)
    {
        // $request = Request::createFromGlobals();
        dump($request);

        return new Response("vous avez $age ans");
    }
```
Ce sont les `arguments resolver`.

## Contraintes de Routes

Précédement, il a été vu une contrainte sur un paramètre, mais il est également possible de mettre une contrainte sur une route.

Imaginons que la route `test` ne peut être appelée que en POST :

Dans un tableau methods, il suffit de placer les contraintes. Ici la route devient innaccessible en `GET`.
```yaml
test:
  path: /test/{age<\d+>?0}
  controller: App\Controller\TestController::test
  methods: [POST]
```
Utile pour une `API` REST, d'ailleurs on pourra rajouter une contrainte sur les host pour controller l'accessibilité.
```yaml
test:
  path: /test/{age<\d+>?0}
  controller: App\Controller\TestController::test
  methods: [POST]
  host: api.monsite.com
```
Ici on controle quel `host` est authorisé pour la route. Comme dans les routes on peut ajouter un paramètre et y accéder suivant plusieurs sous domaines.
```yaml
test:
  path: /test/{age<\d+>?0}
  controller: App\Controller\TestController::test
  methods: [POST]
  host: {subdomain}.monsite.com
```
Une dernière contrainte sur <u><b>la route</b></u>, imaginons une accessibilité uniquement en https en utilisant les `schemes` :
```yaml
test:
  path: /test/{age<\d+>?0}
  controller: App\Controller\TestController::test
  methods: [POST]
  host: {subdomain}.monsite.com
  schemes: [https]
  ```
  <b>Pour résumer les contraintes des paramètres ce sont les requirements ou leurs écriture dans le path, pour les routes ce sont `les methods, les host et les schemes`.</b>

## Oragnistaion des fichiers routes

Il suffit dans le dossier `./config/routes/` de créer un fichier `.yaml` et d'y mettre sa route au même format.

## Les routes sous forme d'Annotations (@Route)

La configuration des routes la plus courante, est l'écriture dans les controlleurs sous forme d'annotation. On reprend exactemet les mêmes infos.
```php
use Symfony\Component\Routing\Annotation\Route;
/**
 * l'annotaion Route represente une class il faut donc l'importer
 * et choisir celle dans le dossier annotation
 * @Route("/test/{age<\d+>?0}", name="test", methods={"GET", "POST",}, host="localhost", schemes={"http", "https"})
 */
```

## Symfony Flex

Complément de composer permet de trouver des packages de symfony qui ne sont pas sur packagist et gère les alias.

## intro container de services Symfony

Un service = une classe, un outil.

Pour les services par défaut, taper la commande :
```sh
php bin/console debug:autowiring
```
Si, par exemple, l'on souhaite rajouter un service de log :
```sh
php bin/console debug:autowiring Log
```
Il faut injecter la dépendance, dans le constructeur :
```php
class HelloController
{
    protected $logger;

    //appel d'un objet qui implémente loggerInterface que l'on nomme $logger
    public function __constrcut(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    public function hello($name)
    {
        $this->logger->info("Message de log !")
        return new Response("Hello $name");
    }
}
```
<b>
<u>Autowiring :</u>

Symfony analyse le constructeur d'une classe pour lui fournir ce qu'elle demande.
</b>

Et Si dans la classe, la méthode est couplée à une route, pas besoin de constructeur, on peut lui injecter directement :
```php
class HelloController
{
    public function hello($name, loggerInterface $logger)
    {
        $logger->info("Message de log !")
        return new Response("Hello $name");
    }
}
```
<b><u>A retenir :</u> Il y a des services dans le framework, ce sont des classes, qui travaillent pour nous.</b>

## Création de nos propres services

Exemple création d'un calculateur de tva, dans un dossier `src/Taxes`, On ^peut écrire nos classes de "logique" qui vont nous servir pour le projet,  ici un simple calcul de TVA en exemple,; qui pouura être uytilisé par tout dans le projet grâce à l'injection de dépendances.

Comme vu précedemment :
```php 
//dans la classe qui doit utiliser notre service charge le namespace
use App\Taxes\Calculator;

// création d'une variable protégée
protected $calculator;
//à la construction du controller passe nous une instance de la class Calculator que l'on nomme $claculator
public function __construct(Calculator $calculator)
{
  $this->calculator = $calculator;
}
//dans une fonction du controller on peut faire un calcul
public function hello($name, loggerInterface $logger)
{
    $logger->error("Mesage de log !");

    $tva = $this->calculator->calcul(100);

    dump($tva);
    
    return new Response("Hello $name");
}
```
<b><u>En résumé:</u> pour les fonctionnements commplexes de certains controller, je peux décentraliser une partie du code dans un service, c'est une 'couche supplémentaire' du conrtolleur que l'on fera ailleurs</b>

Ce qui permettra également de faire de l'`Autowiring`.
## le container de service plus en profondeur

Pour aller plus loin il est important de comprende la notion du contenuer de services. La librairie `symfony/dependency-injection`, nous permet de mieux gérer les injections de dépendances. 
<b>Le container de services "à la responsabilité" d'analyse des classes</b> et leur constructeur => <b>il créé les objets dont on a besoin et injecte lui même les dépendances des classes.</b> <u>Le container de servbices recherche qui a besoin de quoi et fait la construction</u>

## les limites du container de services

Dans `config\services.yaml` on peut définir les services que l'on veut injecter dans le container de services.
Imaginons dans `Taxes/Calculatop.php` (le calculateur de tva) un paramètre fixe $tva, il faut dans ce cas aller le déclarer dans `config\services.yaml`: 
```php
namespace App\Taxes;

use Psr\Log\LoggerInterface;

class Calculator
{
    //declaration des variables en protected
    protected $logger;
    protected $tva;

    //dans le constructeur j'implémente les logs et la tva
    //pour que le container de service comprenne, tva est dans services.yaml
    public function __construct(LoggerInterface $logger, float $tva)
    {
        $this->logger = $logger;
        $this->tva = $tva;
    }
    public function calcul(float $prix) : float
    {
        return $prix * (20 / 100);
    }
}
```
`config\services.yaml`: 
```yaml
  services:
    App\Taxes\Calculator:
        arguments:
            $tva: 20
```
Comment définir dans le container de services des librairies externes ?
Pour l'exemple installons le package `cocur/slugify`.

Imaginons dans le HelloController que l'on souhaite injecter le service sans avoir besoin d'instancier sa classe et d'importer avec un use:
```php
public function hello($name, LoggerInterface $logger, Slugify $slugify)
    {
        $logger->error("Mesage de log !");

        $tva = $this->calculator->calcul(100);

        dump($tva);
        
        return new Response("Hello $name");
    }
```
Dans `config\services.yaml` ajouter le namespace et si rien de particulier un `~`:
```yaml
Cocur\Slugify\Slugify: ~
```
## Différence entre Bundles et librairies

Les <b>bundles sont déclarés dynamiquement</b> dans `config\bundles.php`, le principe reste le même (faire connaitre au container de services de nouveau services).

<u><b>recap:</b></u>

Quand Symfony recoit une requête HTTP:

- il vérifie avec l'urlMatcher que l'url recu correspond à une route. Soit via annotation, soit via `.yaml`. 

- il check quelle fonction est censée répondre

- L'`ArgumentResolver` analyse les parametres qui sont demandés par cette fonction.

Alors 3 possibilités:
1. un paramètre est une instance de `Request`, il sera donc envoyé par symfony
2. un paramètre est dans une route ex: `{prénom}` devient `$prenom`
3. un ou plusieurs paramètres sont des services dans le container
## Découverte de Twig

Du templating classique.
1. Vérifier son installation, au besoin `composer require Twig` (L' alias suffit, Symfony Flex se chargera de trouver pour nous de trouver les bons packages).
2. Faire l'autowiring dans la methode du controller qui en a besoin.
    Symfony se chargera des requires nécessaires pour aller chercher les vues dans le dossier `templates`.
3. Pour passer des variables à la vue, les renseigner sous forme de <u>tableau</u> dans la fonction render.

```php
/**
     * ne pas oublier de mettre $name en parametre de la function
     * @Route("/twig/{name?World}", name="twig")
     */
    public function twig($name, Environment $twig)
    {
        $html = $twig->render('hello.html.twig', ['name' =>$name]);
       return new Response($html);
    }
```

