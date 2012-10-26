# Header 1
## Header 2
### Header 3
#### Header 4
##### Header 5
###### Header 6

---

A link to [ServerGrove.com](http://servergrove.com/ "ServerGrove")

---

Lorem `ipsum` dolor sit *amet*, consectetur **adipiscing** elit. Integer ***fermentum***, nulla vel pharetra porttitor, sapien nulla tincidunt augue, eu ultricies mauris tellus et purus.

* List element 1
* List element 2

---

    A line of code

---

    /**
     * Class PHPExample
     */
    class PHPExample
    {
        /**
         * @var string
         */
        private $property1;

        /**
         * @var boolean
         */
        private $property2;

        /**
         * @param string $property1
         * @param boolean $property2
         */
        public function __construct($property1, $property2)
        {
            $this->property1 = $property1;
            $this->property2 = $property2;
        }

        /**
         * Sets the value of property1
         *
         * @param string $property1
         */
        public function setProperty1($property1)
        {
            $this->property1 = $property1;
        }

        /**
         * Returns the value of property1
         *
         * @return string
         */
        public function getProperty1()
        {
            return $this->property1;
        }

        /**
         * Sets the value of property2
         *
         * @param boolean $property2
         */
        public function setProperty2($property2)
        {
            $this->property2 = $property2;
        }

        /**
         * Returns the value of property2
         *
         * @return boolean
         */
        public function getProperty2()
        {
            return $this->property2;
        }
    }

---

    <html>
        <head>
            <title>Example page</title>
        </head>
        <body>
            Hello World!
        </body>
    </html>
