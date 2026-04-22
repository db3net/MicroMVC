<?php
// controllers/hello.php
class hello extends Controller
{
    public function index(): void
    {
        $this->jsonOutput(['message' => 'Hello from MicroMVC!']);
    }

    public function greet(string $name = 'world'): void
    {
        $this->jsonOutput(['hello' => $name]);
    }
}

?>