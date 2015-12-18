<?php

if(!class_exists('TypeError')) {
  class TypeError extends Error {
    /* Inherited methods */
    abstract public string Throwable::getMessage ( void )
    abstract public int Throwable::getCode ( void )
    abstract public string Throwable::getFile ( void )
    abstract public int Throwable::getLine ( void )
    abstract public array Throwable::getTrace ( void )
    abstract public string Throwable::getTraceAsString ( void )
    abstract public Throwable Throwable::getPrevious ( void )
    abstract public string Throwable::__toString ( void )
  } 
}
