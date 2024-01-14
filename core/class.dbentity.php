<?php

defined("isInSideApplication") ? null : die('no access');

#[AllowDynamicProperties]
class dbentity
{

    private db $db;

    private string $table;

    private array $columns = [];

    private array $columnMap = [];

    private array $columnMapInverse = [];

    private array $columnValues = [];
    private array $columnValuesOriginal = [];
    private array $columnValuesAltered = [];

    private array $primaryKeys = [];

    // Get the original values of primary / unique columns,
    // so that we can update those fields later...
    private array $primaryKeyValues = [];

    private array $virtualFields = [];

    private array $alteredFields = [];

    private bool $wasReadFromDatabase = false;

    public function __construct(string $table = null, $whereClause = null, db $db = null)
    {

        // Did we get a table name?
        if(!$table)
        {
            throw new dbentity_table_not_specified("Table not specified");
        } else {
            $this->table = "`".str_replace(["`", "."], ["", "`.`"], $table)."`";
        }

        $this->db = $db ?? new db();

        // Get the table definition...
        $this->db->preparedQuery("DESCRIBE ".$this->table);
        if($definition = $this->db->fetch_array())
        {
            foreach($definition as $d)
            {
                $f = new dbentity_column($d["Field"], $d["Type"], $d["Null"], $d["Key"], $d["Default"], $d["Extra"]);
                $this->columns[$f->fieldClean] = $f;
                $this->columnMap[$f->fieldClean] = $f->field;
                $this->columnMapInverse[$f->field] = $f->fieldClean;
                $this->columnValues[$f->fieldClean] = null;
                $this->columnValuesOriginal[$f->fieldClean] = null;
                $this->columnValuesAltered[$f->fieldClean] = false;

                // Is this a primary key?
                if($f->isPrimary)
                {
                    $this->primaryKeys[$f->fieldClean] = $f->field;
                }

                // Is this a generated field?
                if($f->isVirtual)
                {
                    $this->virtualFields[$f->fieldClean] = $f->field;
                }
            }

        } else {
            throw new dbentity_database_error($this->db->error());
        }

        // If we were passed a where clause, try and fetch the data from the database...
        if($whereClause)
        {

            // Do we have a single value for the where clause (i.e. id = xxx, or did we get an array, i.e. WHERE a = x AND b = y)
            if(!is_array($whereClause))
            {
                // Do we have at least one primary key defined?
                if($f = $this->getPrimaryKey())
                {
                    $whereClause = [$f->fieldClean => $whereClause];
                } else {
                    throw new dbentity_primary_key_not_defined("Cannot query table using primary key value - table has no primary keys defined");
                }
            }

            if($results = $this->read($whereClause))
            {
                if(!empty($results))
                {
                    $this->refresh($results[0]);
                } else {
                    throw new dbentity_row_not_found();
                }
            } else {
                throw new dbentity_row_not_found($this->db->error());
            }

        }

    }

    private function setFieldsUnaltered()
    {
        foreach($this->columnValuesAltered as $k => $v)
        {
            $this->columnValuesAltered[$k] = false;
        }
    }

    private function findField(string $field): ?dbentity_column
    {
        // Try and find the field...
        $column = $this->columns[$field] ?? $this->columns[$this->columnMapInverse[$field]] ?? null;
        return $column;
    }

    private function castField(dbentity_column $field)
    {
        $type = $field->phpType();
        switch($type["type"])
        {

            case "int":
                return (int)$this->columnValues[$field->fieldClean];

            case "float":
                return (float)$this->columnValues[$field->fieldClean];

            case "bool":
                return (bool)filter_var($this->columnValues[$field->fieldClean], FILTER_VALIDATE_BOOLEAN);

            case "datetime":
                $v = $this->columnValues[$field->fieldClean];
                if(!is_null($v))
                {
                    $d = new \DateTime($v);
                    return $d->format($type["format"]);
                }
                return null;
        }

        return (string)$this->columnValues[$field->fieldClean];

    }

    private function getPrimaryKey(): ?dbentity_column
    {
        if(!empty($this->primaryKeys) && ($f = $this->findField(array_values($this->primaryKeys)[0])))
        {
            return $f;
        }

        return null;
    }

    private function getRowIdentificationFields(): array
    {
        $fields = [];
        if(!empty($this->primaryKeys))
        {
            foreach($this->primaryKeys as $k => $v)
            {
                $fields[$k] = [
                    "field" => $this->findField($k),
                    "value" => $this->columnValuesOriginal[$k],
                ];
            }
        } else {
            foreach($this->columnValuesOriginal as $k => $v)
            {
                if($f = $this->findField($k))
                {
                    if($f->key)
                    {
                        $fields[$k] = [
                            "field" => $f,
                            "value" => $v,
                        ];
                    }
                } else {
                    throw new dbentity_field_not_found($k);
                }
            }
        }

        if(empty($fields))
        {
            throw new dbentity_no_index_defined();
        }

        return $fields;
    }

    private function read(array $clause = []): array
    {
        // Query placeholders...
        $sql = "SELECT * FROM ".$this->table." WHERE //{!--db_entity_where_clause--!}// LIMIT 1";
        $query = [];
        $values = [];

        // Did we get a clause?
        if(empty($clause))
        {
            // Generate the query using the ID fields...
            foreach($this->getRowIdentificationFields() as $c)
            {
                $f = $c["field"];
                $v = $c["value"];
                $query[] = "`".$f->field."` = :".$f->fieldClean;
                $values[$f->fieldClean] = $v;
            }
        } else {
            // Generate the query...
            foreach($clause as $k => $v)
            {
                if($f = $this->findField($k))
                {
                    $query[] = "`".$f->field."` = :".$f->fieldClean;
                    $values[$f->fieldClean] = $v;
                } else {
                    throw new dbentity_field_not_found($k);
                }
            }
        }

        if(empty($query))
        {
            throw new dbentity_no_query_clause();
        }

        // Run the query...
        $this->db->preparedQuery(str_replace("//{!--db_entity_where_clause--!}//", implode(" AND ", $query), $sql), $values);
        if($results = $this->db->fetch_array())
        {
            if(!empty($results))
            {
                $this->wasReadFromDatabase = true;
                return $results;
            } else {
                throw new dbentity_row_not_found();
            }
        } else {
            throw new dbentity_row_not_found($this->db->error());
        }
    }

    private function refresh(array $row = [])
    {
        // Do we need to read the data from the database?
        if(empty($row))
        {
            if($results = $this->read())
            {
                if(!empty($results))
                {
                    $row = $results[0];
                } else {
                    throw new dbentity_row_not_found();
                }
            } else {
                throw new dbentity_row_not_found($this->db->error());
            }
        }

        foreach($row as $k => $v)
        {
            // Set the value if we know about the field,
            // don't throw an exception, just in case the table
            // definition has changed between the class construction
            // and this point...
            if($f = $this->findField($k))
            {
                // Get the current value of primary keys...
                if($f->isPrimary)
                {
                    $this->primaryKeyValues[$f->fieldClean] = $v;
                }

                $this->columnValues[$f->fieldClean] = $v;
                $this->columnValuesOriginal[$f->fieldClean] = $v;
                $this->columnValuesAltered[$f->fieldClean] = false;
            }
        }
    }

    private function write(array $fields = []): bool
    {
        // Query placeholders...
        $sql = "UPDATE ".$this->table." SET //{!--db_entity_set_clause--!}// WHERE //{!--db_entity_where_clause--!}//";
        $set = [];
        $query = [];
        $values = [];

        // Generate the set...
        if(empty($fields))
        {
            foreach($this->columnValuesAltered as $k => $v)
            {
                if($v)
                {
                    $fields[] = $k;
                }
            }
        }
        foreach($fields as $k)
        {
            if($f = $this->findField($k))
            {
                if(!$f->isVirtual)
                {
                    $set[] = "`".$f->field."` = :set_".$f->fieldClean;
                    $values["set_".$f->fieldClean] = (is_bool($this->columnValues[$f->fieldClean]) ? (int)$this->columnValues[$f->fieldClean] : $this->columnValues[$f->fieldClean]);
                }
            } else {
                throw new dbentity_field_not_found($k);
            }
        }

        // Generate the query...
        foreach($this->getRowIdentificationFields() as $c)
        {
            $f = $c["field"];
            $v = $c["value"];
            $query[] = "`".$f->field."` = :".$f->fieldClean;
            $values[$f->fieldClean] = $v;
        }

        if(empty($query))
        {
            throw new dbentity_no_query_clause();
        }

        // Run the query...
        $this->db->preparedQuery(str_replace(["//{!--db_entity_set_clause--!}//", "//{!--db_entity_where_clause--!}//"], [implode(", ", $set), implode(" AND ", $query)], $sql), $values);
        // Mark the fields as unaltered...
        if($this->db->affected_rows())
        {
            $this->setFieldsUnaltered();
        }
        return (bool)$this->db->affected_rows();
    }

    private function insert(): bool
    {
        // Query placeholders...
        $sql = "INSERT INTO ".$this->table." (//{!--db_entity_columns_clause--!}//) VALUES (//{!--db_entity_values_clause--!}//)";
        $columns = [];
        $values = [];
        $query = [];

        // Generate the clauses...
        foreach($this->columnValuesAltered as $k => $v)
        {
            if($v)
            {
                if($f = $this->findField($k))
                {
                    if(!$f->isVirtual)
                    {
                        $columns[] = "`".$f->field."`";
                        $values[] = ":".$f->fieldClean;
                        $query[$f->fieldClean] = (is_bool($this->columnValues[$f->fieldClean]) ? (int)$this->columnValues[$f->fieldClean] : $this->columnValues[$f->fieldClean]);
                    }
                } else {
                    throw new dbentity_field_not_found($k);
                }
            }
        }

        if(empty($columns))
        {
            throw new dbentity_no_values_to_update_or_set();
        }

        // Run the query...
        $this->db->preparedQuery(str_replace(["//{!--db_entity_columns_clause--!}//", "//{!--db_entity_values_clause--!}//"], [implode(", ", $columns), implode(", ", $values)], $sql), $query);
        if($this->db->affected_rows())
        {
            // Set the primary key value...
            if($this->db->insert_id() && ($f = $this->getPrimaryKey()))
            {
                $this->columnValues[$f->fieldClean] = $this->db->insert_id();
                $this->columnValuesOriginal[$f->fieldClean] = $this->db->insert_id();
            }

            // Mark the fields as unaltered...
            $this->setFieldsUnaltered();

            return true;

        } else {
            throw new dbentity_database_error($this->db->error());
        }

        return false;

    }

    public function __get(string $field)
    {
        if($f = $this->findField($field))
        {
            return $this->castField($f);
        } else {
            throw new dbentity_field_not_found($field);
        }
    }

    public function __set(string $field, $value = null)
    {
        if($f = $this->findField($field))
        {
            // Is this a generated column?
            if($f->isVirtual)
            {
                throw new dbentity_field_value_not_allowed($field." is a generated column");
            }

            // Is this field nullable?
            if(is_null($value) && !$f->null)
            {
                throw new dbentity_field_value_not_allowed($field." cannot be null");
            }

            // Is this an enum / set?
            if(!empty($f->allowedValues) && !in_array($value, $f->allowedValues))
            {
                throw new dbentity_field_value_not_allowed("Value must be one of: ".implode(", ", $f->allowedValues));
            }

            // Does this field have a maximum length?
            if($f->length && strlen($value) > $f->length)
            {
                throw new dbentity_field_length_exceeded();
            }

            // ToDo: Need to match value passed to allowed field type (i.e. int vs string)
            $this->columnValues[$f->fieldClean] = $value;
            $this->columnValuesAltered[$f->fieldClean] = true;

        } else {
            throw new dbentity_field_not_found($field);
        }
    }

    public function __isset(string $field)
    {
        if($f = $this->findField($field))
        {
            return true;
        }
        return false;
    }

    public function __unset(string $field)
    {
        if($f = $this->findField($field))
        {
            // Is this a generated column?
            if($f->isVirtual)
            {
                throw new dbentity_field_value_not_allowed($field." is a generated column");
            }

            // Is this field nullable?
            if(!$f->null)
            {
                throw new dbentity_field_value_not_allowed($field." cannot be null");
            }

            $this->columnValues[$f->fieldClean] = null;
            $this->columnValuesAltered[$f->fieldClean] = true;

        } else {
            throw new dbentity_field_not_found($field);
        }
    }

    public function __toString()
    {
        $j = [];
        foreach($this->columnValues as $k => $v)
        {
            if($f = $this->findField($k))
            {
                $j[$f->fieldClean] = $this->castField($f);
            }
        }
        return json_encode($j);
    }

    public function save(): bool
    {
        if($this->wasReadFromDatabase)
        {
            $result = $this->write();
        } else {
            $result = $this->insert();
        }

        if($result)
        {
            $this->refresh();
            return true;
        }

        return false;

    }

}

// Table column class...
class dbentity_column
{
    public string $field;
    public string $fieldClean;
    public string $type;
    private string $typeOriginal;
    public int $length = 0;
    public bool $null = false;
    public ?string $key;
    public ?string $default;
    public array $allowedValues = [];
    public ?string $extra;
    public bool $isPrimary = false;
    public bool $isUnique = false;
    public bool $isVirtual = false;

    private array $typeMappings = [

        // Integers
        "tinyint" => ["type" => "int"],
        "smallint" => ["type" => "int"],
        "mediumint" => ["type" => "int"],
        "int" => ["type" => "int"],
        "bigint" => ["type" => "int"],

        // Floats
        "decimal" => ["type" => "float"],
        "float" => ["type" => "float"],
        "double" => ["type" => "float"],
        "real" => ["type" => "float"],

        // Booleans
        "boolean" => ["type" => "bool"],

        // Dates / Times
        "date" => ["type" => "datetime", "format" => "Y-m-d"],
        "datetime" => ["type" => "datetime", "format" => "Y-m-d H:i:s.u"],
        "timestamp" => ["type" => "datetime", "format" => "Y-m-d H:i:s.u"],
        "time" => ["type" => "datetime", "format" => "H:i:s.u"],
        "year" => ["type" => "datetime", "format" => "Y"],

    ];

    public function __construct($field, $type, $null, $key, $default, $extra)
    {
        // Data from database...
        $this->field = $field;
        $this->typeOriginal = $type;
        $type = explode("(", trim(str_replace([")", "unsigned", "UNSIGNED"], "", $type)));
        $this->type = strtolower($type[0]);
        // Is this a length or a set?
        if(count($type) > 1)
        {
            if(strpos($type[1], "'") !== false || strpos($type[1], ",") !== false)
            {
                $this->allowedValues = explode(",", str_replace("'", "", $type[1]));
            } else {
                $this->length = (int)$type[1];
            }
        }
        $this->null = filter_var(strtolower($null), FILTER_VALIDATE_BOOLEAN);
        $this->key = $key;
        $this->default = $default;
        $this->extra = $extra;
        
        // Our modified data...
        $this->fieldClean = preg_replace_callback("/(_+[A-Za-z0-9])/", function($matches) {
            return str_replace("_", "", strtoupper($matches[0]));
        }, str_replace(" ", "_", preg_replace("/[^A-Za-z0-9 _]/", "", $this->field)));
        $this->isPrimary = (strpos(strtolower($key), "pri") !== false ? true : false);
        $this->isUnique = (strpos(strtolower($key), "uni") !== false ? true : false);
        $this->isVirtual = (strpos(strtolower($extra), " generated") !== false ? true : false);
    }

    public function phpType(): array
    {
        return $this->typeMappings[$this->type] ?? ["type" => "string"];
    }

}

// Exception classes...

class dbentity_exception extends \Exception
{

}

class dbentity_table_not_specified extends dbentity_exception
{

}

class dbentity_database_error extends dbentity_exception
{

}

class dbentity_no_query_clause extends dbentity_exception
{

}

class dbentity_field_not_found extends dbentity_exception
{

}

class dbentity_field_value_not_allowed extends dbentity_exception
{

}

class dbentity_field_length_exceeded extends dbentity_exception
{

}

class dbentity_primary_key_not_defined extends dbentity_exception
{

}

class dbentity_no_index_defined extends dbentity_exception
{

}

class dbentity_row_not_found extends dbentity_exception
{

}

class dbentity_no_values_to_update_or_set extends dbentity_exception
{

}