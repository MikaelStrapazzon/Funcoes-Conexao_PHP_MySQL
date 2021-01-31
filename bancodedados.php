<?php
	/**
	 * Conecta ou reutiliza a conexao com o banco de dados, retornando a mesma
	 * @return object|string Conexao com o banco de dados ou o erro
	 */
	function bd_connect()
	{
		static $conection;

		if( !$conection )
			$conection =
				new mysqli(
					"localhost",
					"root",
					"",
					"testeFuncoes"
				);

		if( $conection->connect_errno )
			return $conection->connect_errno . ": " . $conection->connect_error;

		return $conection;
	}

	/**
	 * Executa um SQL no banco e retorna o resultado
	 * @param string $sql SQL que vai ser executado (obs: Deve ter o coringa [%N] nos valores a ser tratado)
	 * @param array $values Valores a serem tratados no SQL (O valor é substituido sempre no coringa posterior. ex: índice 0 vai ser substituido no coringa [%1])
	 * @return object|boolean|string
	 * 	Objeto se for um SELECT, SHOW, DESCRIBE ou EXPLAIN executado com sucesso
	 * 	Boolean TRUE se for outro tipo executado com sucesso
	 * 	String com o erro se houver
	 */
	function bd_query( $sql, $values = array() )
	{
		$conection = bd_connect();

		if( !is_object( $conection ) )
			return $conection;
		elseif( !is_array( $values ) )
			return "Valores passados no formato incorreto!";

		$wildcard = array();

		foreach( $values as $index => &$value )
		{
			$value = $conection->real_escape_string( $value );
			$wildcard[] = "[%" . ++$index . "]";
		}

		$sql = str_replace( $wildcard, $values, $sql );

		$result = $conection->query( $sql );

		if( $result )
			return $result;
		else
			return $conection->errno . ": " . $conection->error;
	}

	/**
	 * Executa um SQL do tipo SELECT no banco e retorna o resultado
	 * @param string $sql SQL que vai ser executado (obs: Deve ter o coringa [%N] nos valores a ser tratado)
	 * @param array $values Valores a serem tratados no SQL (O valor é substituido sempre no coringa posterior. ex: índice 0 vai ser substituido no coringa [%1])
	 * @return array|string
	 * 	Array com o resultado do SELECT (array vazio se não retornar nada)
	 * String erro caso ocorra
	 */
	function bd_select( $sql, $values = array() )
	{
		$result = bd_query( $sql, $values );

		if( is_string( $result ) )
			return $result;

		$return = array();

		while( $row = $result->fetch_assoc() )
			$return[] = $row;

		return $return;
	}