# hash-posts
This is a Facebook application that enables a user to parse all hashtags in a Facebook post and automatically tweets those hashtags in the user's Twitter wall.
This uses the facebook webhooks to keep track of when a user has posted on his wall, negating the use running cron jobs to keep track of it.
This uses a MySQL database to store the user details (FBID, TwitterID) alongwith the Facebook and Twitter Access tokens. 
Even though the Facebook tokens are long lived, they last for only 60 days. To remove this dependency, the FB tokens are updated every week so that the user doesn't have to log back in again.

To Intall - 
- Clone the Directory
- In the root folder, run composer install
- Open headers.php and enter your APP details (Both for Facebook and Twitter)
- Create a new mysql database using the schema provided in config/selective_posts.sql
