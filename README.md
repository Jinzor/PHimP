# PHimP

Simple PHP template using MVC architecture.

### Installation

Clone the repo and run

```
composer install
```

```
npm install
```

To generate js (babel) use

```
npm run build 
```


### SCSS

```
scss assets/scss/style.scss assets/css/style.css
```

### Rewriting

Use .htaccess to rewrite urls.

The *?p=* parameter is use to define the page, separating with a dot to call the Controller.

Example
> `index.php?p=app.index` will call AppController and the index() method.
