<?php

use Illuminate\Database\Seeder;
use App\Author;

class BasicAuthorsSeeder extends Seeder
{

    private $author_id;
    private $author_inserted_id;

    public function run()
    {

        // Import Authors
        $authors = \DB::connection('shamela_access')->table('auth')->get();

        foreach ($authors as $author) {
            $this->importAuthor($author);
        }
    }

    public function importAuthor($imported_author)
    {
        // If found don't insert
        if (!Author::find($imported_author->authid)) {

            $object_array = (array)$imported_author;

            $object = [];
            $object['id'] = $object_array['authid'];
            $object['name'] = $object_array['auth'];
            $object['abstract'] = $object_array['inf'];
            $object['fullname'] = $object_array['Lng'];
            $object['hijri_date'] = $object_array['HigriD'];
            $object['seal'] = $object_array['seal'];

            Author::insert($object);
        }
    }
}
