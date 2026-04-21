<?php
// controllers/hello.php
class hello extends Controller
{
    public function index(): void
    {
        $this->json_output(['message' => 'Hello from MicroMVC!']);
    }

    public function greet(string $name = 'world'): void
    {
        $this->json_output(['hello' => $name]);
    }
}

?>