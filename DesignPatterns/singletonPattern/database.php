<!DOCTYPE html>
<html>
<body>

<?php
	
class Database{
	
	private static $instance = null;
    private $connection;
    
   	private function __construct(){
    	$this->connection = new PDO("mysql: host=localhost;dbname=test", "root", 			"");
        
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public static function getInstance(){
    	if(self::$instance === null) {
        	self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection(){
    	return $this->connection;
    }
}
?> 


<!-- class Database { -->

Defines a class named Database.

A class is like a blueprint for creating objects (in this case, a database manager).

<!-- private static $instance = null; -->

private → no external tampering.

static → only one copy shared by the whole class.

$instance → holds the one object.

= null → ensures lazy creation (created only when needed).

<!-- private $connection; -->

🔹 Why we need $connection

$connection holds the PDO object (the actual connection to the database).

If we don’t store it, the connection would be lost after the constructor finishes.

By keeping it in a class property, we can reuse the same DB connection whenever we need.

🔹 Why it is private

Encapsulation → we don’t want outside code to directly modify or overwrite the database connection.

$db = Database::getInstance();
$db->connection = null; // ❌ would break everything if public


By making it private, only the class controls the connection.

Controlled Access → instead of letting others touch it directly, we give them a method:

$conn = Database::getInstance()->getConnection();


This way, we can control how and when the connection is used.

Singleton Integrity → if $connection were public, someone could replace it with a new PDO connection, defeating the point of having a single consistent connection.

<!-- private function __construct() { -->
Mistake here: it should be __construct (double underscore), not _construct. Otherwise, PHP will not call it automatically.

Normal __construct()

Normally, a constructor is public:

class A {
    public function __construct() {
        echo "Object created";
    }
}

$a1 = new A(); // ✅ works
$a2 = new A(); // ✅ another object created


➡️ Anyone can create as many objects as they want.

🔹 In Singleton → private function __construct()
class Database {
    private static $instance = null;
    private function __construct() {
        echo "Database connected";
    }
}


Now:

$db = new Database(); // ❌ ERROR: Call to private constructor


➡️ Nobody outside the class can create an object directly.

🔹 Why make it private?

Restrict Object Creation

Singleton means only one object should exist.

If constructor is public, anyone can call new Database() and make multiple objects.

private stops this.

Force controlled access

The only way to get the object is through getInstance().

Inside getInstance(), we check:

if (self::$instance === null) {
    self::$instance = new Database(); // constructor used only once here
}


This ensures only one object is ever created.

Encapsulation of setup

The constructor might have sensitive code (like DB connection, API keys, configs).

Making it private ensures no external script messes with initialization.
Without a private constructor, Singleton doesn’t work — because anyone can just do new Database() as many times as they want.


<!-- What is self in PHP? -->

self is a special keyword in PHP used inside a class.

It refers to the class itself, not an object (instance) of the class.

It is used to access:

Static properties

Static methods

Constants

<!-- Why not $this? -->

self → Refers to class itself (for static context).

$this → Refers to current object (instance) of the class.
class Test {
    public static $count = 0;
    public $name = "PHP";

    public function wrong() {
        echo $this->count;   // ❌ Error: $count is static
    }

    public function correct() {
        echo self::$count;   // ✅ Correct
        echo $this->name;    // ✅ Correct
    }
}

<!-- Why private static $instance -->

static

Belongs to the class itself, not to objects.

Ensures there’s only one $instance variable shared across the whole app.

If it were not static, each object could have its own $instance, which breaks Singleton.

private

Prevents outside code from doing this:

Database::$instance = new Database(); // ❌ breaks Singleton


Only the class can manage its $instance.

So → only one instance of Database is kept, and only the class controls it.

🔹 Why public static function getInstance()

This is the only way to access the object.

It is:

public → so any external code can use it.

static → so we don’t need to create an object first (since we don’t have one yet).

➡️ If it were not static, you’d need:

$db = new Database();     // ❌ Not allowed, constructor is private
$db->getInstance();


But you can’t create an object because the constructor is private!
So it must be static to be called like this:

$db = Database::getInstance();

🔹 How it works step by step

First call:

$db1 = Database::getInstance();


$instance is null → create a new Database() object.

Store it in self::$instance.

Return it.

Next call:

$db2 = Database::getInstance();


$instance is already set.

Return the same object, not a new one.

So $db1 and $db2 point to the same object.

🔹 Why not just make $instance public?

If $instance were public, anyone could overwrite it:

Database::$instance = null;       // breaks singleton
Database::$instance = new Other(); // breaks logic


That’s why it’s private and controlled only through getInstance().
Why Design B is not ideal

Singleton becomes meaningless

The whole purpose of Singleton is to guarantee one object.

In Design B, you don’t really use that object anymore.

Everything important ($connection) is static → you might as well skip Singleton and make the whole class static.

Example:

$conn1 = Database::getConnection();
$conn2 = Database::getConnection();


Here you never even touch the object, but Singleton was supposed to be about controlling that object.

Flexibility loss

In Design A, if one day you want to extend the class, or allow multiple connections (say one for MySQL, one for SQLite), you can — because the connection belongs to an object.

In Design B, you locked everything at the class level. To allow multiple connections, you’d need to rewrite the whole design.

Encapsulation is weaker

In Design A:

The connection is safely tied to the life cycle of the object.

In Design B:

The connection exists outside of any object (class-level), which is closer to global variables — and global variables are usually bad for design because they make code harder to manage/test.
</body>
</html>

