<?php

    class Evil_Exception_ApiExeption implements Evil_Exception_Interface
    {
        public function __invoke($message)
        {
			echo $message;
            
        }
    }