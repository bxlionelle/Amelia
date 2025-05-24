Amelia - Ecommerce 

⚙️ Project Setup Instructions
1. Clone the project
2. Copy .env.example into .env and configure database credentials
3. Navigate to the project's root directory using terminal
4. Run `composer install`
5. Set the encryption key by executing `php artisan key:generate`
6. Run migrations `php artisan migrate --seed`
7. Run data seeder to test ``` php artisan db:seed AdminSeeder``` and and other db seeder files you can find under database/seeders
7. Start local server by executing `php artisan serve`
8. Open new terminal:
   Run `npm install`
9. Run `npm run dev` to start vite server for Laravel frontend
10. For Stripe Api key, please go to .env file and replace with your api key


🚀 Technologies Use
🖥️ Languages

    PHP

🧰 Frameworks & Tools

    Laravel – Main PHP framework
    
    Symfony – Utilized via Laravel components
    
    PHPUnit – Unit testing
    
    Inertia.js – Modern single-page app support using Vue.js with Laravel

🔌 APIs & Libraries

    Stripe API – For payment and subscriptions
    
    Carbon – Date/time handling
    
    Guzzle – HTTP requests
    
    Monolog – Logging
    
    Faker – Fake data for testing
    
    Mockery – Mocking framework for testing
    
    Spatie – Various Laravel packages (permissions, media, etc.)
    
    Doctrine – Used internally by Laravel (e.g. DBAL)
    
    PSR – PHP Standards Recommendations
    
    ...and other Laravel ecosystem tools


👥 Project Contributors & Roles
| Name                  | Role(s)                               |
| --------------------- | ------------------------------------- |
| **Lionelle Barayuga** | Programmer, Backend Developer, Tester |
| **Carol Egonio**      | Programmer, Backend Developer, Tester |
| **Jade Babiano**      | Frontend Developer, Project Manager   |
| **Felizardo Batula**  | Frontend Developer, Tester            |
| **Vincent Caspe**     | Frontend Developer                    |
