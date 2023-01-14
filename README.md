<p align="center"><a href="https://symfony.com" target="_blank"><img src="https://symfony.com/logos/symfony_black_02.svg" width="400" alt="Symfony Logo"></a></p>


# Symfony template

Tento template je vhodné si naklonovat rovnou už v GitLabu, protože
projekt už bude mít pojmenování podle vašeho přání.
Součástí template je i konfigurace DOCKERU.

## Import do nového projektu krok 1
Zde vybereme "Import projekt".

![Path mapping](./docker/img/importDoNovehoProjektu.png)

## Import do nového projektu krok 2
Zde vybereme "Repo by URL.

![Path mapping](./docker/img/importProjektu.png)

## Import do nového projektu krok 3
Vyplnění údajů pro import template do vašeho nového projektu:
##### 1, Git repository URL - URL zdrojového repozitáře (můj template)
##### 2, Username (optional) - vaše přihlašovací jméno
##### 3, Password (optional) - vaše přihlašovací heslo
##### 4, Project name - jméno nového projektu
##### 5, Visibility Level - viditelnost projektu

![Path mapping](./docker/img/nastaveniImportu.png)

## Nastavení xdebug


Nastavení debug a portů:

![](docker/img/img1.png)


Nastavení cesty k projektu a uvnitř kontejneru:

![](docker/img/img2.png)

## Základní příkazy pro konfiguraci kontejneru

#### Build image
```sh
docker-compose build
```
Toto by melo stahnout a nastavit vše potřebné

#### Sestaveni a spustení kontejneru
```sh
docker-compose up  -d nebo
docker-compose up -d --build
```
V tuto chvili by mel byt docker projekt ready na http://localhost:85 viz soubor docker-compose.yml,
kde to jde i změnit. --build používáme pro přegenerování image kontejneru.
```sh
http://localhost:85
```

#### ukončení kontejneru
```sh
docker-compose down  
```
Potom je možné i na stejný port spustit jiný kontejner.

## SQL databáze a adminer

Součásti konfigurace je SQL databáze a adminer. Najdeme jí na url:
```sh
http://localhost:85/adminer
```
Databáze mezi kontejnery je na portu 3306 viz soubor docker-compose.yml.
Není potřeba psát do připojení k databázi IP a port. Stačí název service
ze souboru docker-compose.yml vč. jména root a hesla.
```sh
mysqldb
```
Neukládejte na Gitlab soubory DB, pouze sql dump. Jedná se o adresář ./data/database, který musí zůstat
git ignore. 

## Konfigurace routování

Konfigurace routování, směrování je aktuálně v template nastaveno přes anotace
u jednotlivých method Controlleru, kde se nastavuje obsluhovaná URL a pojmenovává
routa.

```sh
     /**
     * @Route("/form-add/{id}", defaults={"id" = null}, name="homepage_form_add") 
     * @param Request $request
     * @param ManagerRegistry $doctrine
     * @return Response
     */
         public function formAdd(string $id = null, Request $request, ManagerRegistry $doctrine): Response
    {
```

## Vytváření odkazů

Odkazy vytváříme na pojmenované routy v anotacích. Používáme je i k odkazům na CSS a JS.

```sh
        <link rel="stylesheet" href="{{ url('homepage') }}layouts/{{ layout }}/css/bootstrap.css">
        <a class="dropdown-item" href="{{ path('homepage', {_locale: 'de'}) }}">Deuche</a>
```

## Šablonovací systém "Twig"

Šablony jsou v adresáři templates a pojmenování má formát nazevsablony.html.twig.
Když je šablona v podadresáří, voláme nazevadresare.nazevsablony.html.twig.

dědění layoutové šablony:
```sh
{% extends 'base.html.twig' %}
```

Include - načtení externí šablony
```sh
{% include 'navbar.html.twig' %}
```

označení bloku html contentu, co se includuje podle příslušné akce controlleru do layoutu:
```sh
{% block body %}
// nejaké html pro content
{% endblock %}
```

příklad vložení contentu jednotlivých stránek do layoutu:
```sh
    {% block body %}
    {% endblock %}
```

pro případ vložení nějakého js nebo css konkrétní stránky do layoutu:
```sh
    {% block css %}
    {% endblock %}
    {% block js %}
    {% endblock %}
```

příklad výpisu proměnné v layoutu:
```sh
{{ id }}
```

příklad generování odkazu na pojmenovanou routu, kterou vložíme třeba do href html tagu a pod:
```sh
{{ path('homepage_detail') }}
```

příklad vložení části stránky:
```sh
{% include 'navbar.html.twig' %}
```


vložení js nebo css z příslušné šablony do layoutu:
```sh
    {% block css %}
    {% endblock %}
    {% block js %}
    {% endblock %}
```

příklad podmínky v layoutu:
```sh
{% if data is iterable %}
{% endif %}
```

příklad cyklu v layoutu:
```sh
{% for key, value in data %}
{{ value.street }} // přístup v poli v cyklu

      {% if loop.last %} // v cyklu můžeme použít i iterátor.
          <hr style="border: 1px solid red">
      {% endif %}

{% endfor %}
```

Vypsání formuláře v šabloně

```sh
 {{ form(form) }}
```

## Nastavení přístupu k DB přes doctrine


Nastavujeme v souboru env na root webu
```sh
DATABASE_URL="mysql://pokus:pokus@mysqldb:3306/pokus?serverVersion=mariadb-10.7.3&charset=utf8mb4"
```

## Základní příkazy pro doctrine
Příkazy zadáváme v docker containeru

#### vypiše seznam příkazů
```sh
php bin/console list make
```

#### vytvoreni entity
```sh
php bin/console make:entity
```
Pro relaci v průvodci vytvoříme "type relation"

```sh
php bin/console make:migration
```
Připraví sql k migraci

```sh
php bin/console doctrine:migrations:migrate
```
Vytvoří tabulky v DB

#### vytvoření entit z existující tabulky
```sh
php bin/console doctrine:mapping:import "App\Entity" annotation --path=src/Entity
```
Prvním krokem k vytvoření tříd entit z existující databáze je požádat Doctrine, aby 
si databázi prohlédla a vygenerovala odpovídající soubory metadat. 
Soubory metadat popisují třídu entity, která se má generovat na základě polí tabulky.

Tento nástroj příkazového řádku žádá Doctrinu, aby prozkoumala databázi a vygenerovala 
nové třídy PHP s metadaty anotací do src/Entity. Tím se vygenerují dva soubory: 
BlogPost.phpa BlogComment.php.

#### úpravy existujících entit
```sh
php bin/console make:entity --regenerate
```
Regeneruje entity

```sh
php bin/console make:migration
```
Připraví sql k migraci

```sh
php bin/console doctrine:migrations:migrate
```
Vytvoří/upraví tabulky v DB

```sh
bin/console doctrine:migrations:sync-metadata-storage
```
Synchronizuje metadata


#### všechny příkazy
```sh
doctrine:migrations:current [aktuální] Vypíše aktuální verzi.
doctrine:migrations:diff [diff] Vygenerujte migraci porovnáním vaší aktuální databáze s vašimi mapovacími informacemi.
doctrine:migrations:dump-schema [schéma výpisu] Vypište schéma pro vaši databázi do migrace.
doctrine:migrations:execute [execute] Manuální spuštění jedné verze migrace nahoru nebo dolů.
octrine:migrations:generate [generate] Vygeneruje prázdnou třídu migrace.
doctrine:migrations:latest [latest] Vypíše číslo nejnovější verze
doctrine:migrations:migrate [migrate] Proveďte migraci na zadanou verzi nebo na nejnovější dostupnou verzi.
doctrine:migrations:rollup [souhrnná] Shrňte migrace odstraněním všech sledovaných verzí a vložením jediné verze, která existuje.
doctrine:migrations:status [stav] Zobrazení stavu sady migrací.
doctrine:migrations:up-to-date [up-to-date] Řekne vám, zda je vaše schéma aktuální.
doctrine:migrations:version [verze] Ručně přidat a odstranit verze migrace z tabulky verzí.
doctrine:migrations:sync-metadata-storage [sync-metadata-storage] Zajišťuje, že úložiště metadat je v nejnovější verzi.
doctrine:migrations:list [list-migrations] Zobrazí seznam všech dostupných migrací a jejich stav.
```

## Základní příkazy a metody pro tvorbu formulářu
Příkazy zadáváme v docker containeru

#### 
```sh
php bin/console make:form
```

V prvním kroku zadáme název formuláře a ve druhém kroku název entity, ze které bude formulář vygenerován. Vygeneruje
nám to formulář, který bude potřeba dále dodělat. Formulář se vygeneruje do adresáře src/Form/NazevFormulareFormType.php
V metodě "build" provedeme úprqvy.


#### Příklad vygenerovaného formulářového elementu:

Můžeme v jednotlivých elementech nastavovat např. required, class pro stylování, 
validaci elementu a počet znaků, které je možné do elementu vyplnit. Název elementu
nebo typ. Náš vzorový element se jmenuje "street", a je typ "Textarea".

```sh
->add('street', TextareaType::class, ['required' => true,  
                'row_attr' => ['class' => 'form-group is-invalid'],
                'attr' => ['maxlength' => 4
                    //, 'novalidate' => 'novalidate']
                ]])
```

##### Příklad manuální úpravy formulářů:

U formulářů můžeme např. nastavit i třidy css pro html, omezit počet znaků v elementu a 
vypnout nebo zapnout validaci.

```sh
'required' => true  // nutno vyplnit
'row_attr' => ['class' => 'form-group is-invalid'] // príklad nastavení dvou class v html
'novalidate' => 'novalidate' // vypnutí validace
'attr' => ['maxlength' => 4 // maximální počet znaků v elementu
'placeholder' => '--- Vyber mesto ---' // příklad placeholder
'label' => 'Mesto'
'row_attr' => ['class' => 'form-group is-invalid',]
'label_attr' => ['class' => 'mt-4'] // nastavení html class pro popisek formuláře
```

Příklad nastavení formulářového elementu, název a typ elementu.

```sh
>add('street', TextareaType::class  // element se jmenuje "street" a typ je textarea
```

V případě, že entita ze které byla tabulka generována má relaci do jiné tabulky a je potřeba
input select, upravíme element takto. Příklad pro relaci do tabulky Cities

```sh
->add('cities', EntityType::class, [  // misto typu elementu zapíšeme EntityType::class
     'placeholder' => '--- Vyber mesto ---', 
      'class' => Cities::class, // nazev entity kam je odkazováno
```

Vytvoření formuláře v Controleru v příslušné akci

```sh
$form = $this->createForm(AddressFormType::class, $Eaddress,
     [
        'action' => $this->generateUrl('homepage_form_add'),
        'method' => 'POST'
      ])->handleRequest($request);
```


Metody pro kontrolu stavu odeslání a validity formuláře

```sh
$form->isSubmitted()
$form->isValid()
```

Uložení do zprávy flash messages

```sh
$this->addFlash("success", 'Data ulozena');
```

Přesměrování na jinou stránku po uložení dat.

```sh
return $this->redirect($this->generateUrl('homepage_default'));
```

Příklad zpracování po odesláhní formuláře
```sh
public function formAgg(string $id = null, Request $request, ManagerRegistry $doctrine): Response

            // ziskani dat z formulare
            $data = $form->getData();

            $em = $doctrine->getManager();
            $em->persist($data);
            $em->flush();
```
