# TodoList Symfony API and React

## Prerequisites
- PHP 7.4 or superior
- Composer (Install Composer: https://getcomposer.org/download/)
- Node.js et npm (Install Node.js: https://nodejs.org/)

### Installation Instructions

#### 1. Clone the GitHub repository :
- ```git clone https://github.com/MickaelSayer/TodoList---symfonyApi-React.git```

#### 2. Install Symfony dependencies :
- ```composer install```

#### 3. Install NPM dependencies :
- ```npm install```

#### 4. Create the .env.local file :
- Update : APP_ENV
- Update : MAILER_DSN
- Update : DATABASE_URL

#### 5. Generate the JWT key :
- Create a JWT folder in the config directory
- Bash : Generate the private key - ```openssl genrsa -out config/JWT/private.pem 2048```
- Bash : Generate the public key - ```openssl rsa -in config/JWT/private.pem -pubout > config/JWT/public.pem```

#### 6. Create the database :
- ```php bin/console doctrine:database:create```

#### 7. Execute the migrations :
- ```php bin/console doctrine:migrations:migrate```

#### 8. Load the fixtures :
- ```php bin/console doctrine:fixtures:load```

#### 9. Launch the local Symfony server :
- ```symfony server start```

#### 10. Launch the React server :
- ```npm run dev-server -- --port 9000```

#### 11. Launch the SMTP server for emails :
- ```maildev```

The project is now ready to be used locally. 
Visit http://localhost:8000 to access the Symfony application and http://localhost:9000 for the React application. 
For emails, check the SMTP server at http://localhost:1080.