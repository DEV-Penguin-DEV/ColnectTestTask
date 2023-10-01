# HTML Element counter

### How to start project

#### 1. Go into frontend folder

```
cd frontend/
```

#### 2. Install all dependencies

```
npm i
```

#### 3. Create build of project DEV/Product

Create development build folder

```
npm run dev
```

Create product build folder

```
npm run build
```

#### 4. Start localhost with php and mysql

During development I was using
`Localhost` - MAMP
`PHP` - 7.4.33
`MySQL version` - 5.7.39

#### 5. Start localhost with php and mysql

Open browser and go to http://localhost:{PORT}/phpMyAdmin/

- Create a new database or use created but empty
- Import sql file which be `backend/elements_counter.sql`
- Go to `backend/config.php` and put our DB name, user password, user name, server name

#### 6. Start localhost with php and mysql

Open browser and go to http://localhost:{PORT}/front/build/

### Other project scripts

```
# Run linter check
npm run lint

# Run linter auto fix
npm run lint:fix
```

### Other info about project

### Project structure

`Frontend (front/):`

- `/public/` - Static data
- `/src/js` - JavaScipt files
- `/src/scss` - Styles with scss preprocessor
- `/build/` - Output folder which created by webpack

`Backend (backend/):`

- `/config.php` - Database config
- `/functions.php` - All needed functions
- `/elements_counter.sql` - Database schema
- `/elements_counter.php` - Main backend file which be a endpoint for searching request from front part

#### Techical stack

`Frontend:`

- HTML
- SCSS
- JavaScript (ES8)
- Webpack
- Git
- eslint

`Backend:`

- PHP7
- MySQL 5.7

#### List of browser where it was tested

- Google Chrome
- Safari
