<?php
 
class Conexao {
 
    private static ?PDO $instancia = null;
 
    private const HOST   = 'localhost';
    private const BANCO  = 'musa_music';
    private const USUARIO = 'root';
    private const SENHA  = '';      
    private const PORTA  = '3306';
 
    private function __construct() {}
 
    public static function getInstance(): PDO {
        if (self::$instancia === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                self::HOST,
                self::PORTA,
                self::BANCO
            );
 
            try {
                self::$instancia = new PDO($dsn, self::USUARIO, self::SENHA, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                die("Erro na conexão com o banco: " . $e->getMessage());
            }
        }
 
        return self::$instancia;
    }
}