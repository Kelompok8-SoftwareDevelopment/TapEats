<?php

namespace App\Models;

trait Search
{
    private function buildTsQuery($term)
    {
        // Hilangkan karakter khusus yang nggak perlu
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        $term = str_replace($reservedSymbols, ' ', $term);

        // Pecah jadi kata, lalu gabungkan dengan operator & (AND)
        $words = array_filter(explode(' ', $term));
        $queryParts = array_map(function ($word) {
            return $word . ':*'; // menggunakan prefix matching
        }, $words);

        return implode(' & ', $queryParts);
    }

    protected function scopeSearch($query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        $columns = $this->searchable;

        // Buat expression to_tsvector untuk gabungan kolom
        // Contoh: to_tsvector('english', coalesce(col1, '') || ' ' || coalesce(col2, '') ...)
        $tsVectorParts = array_map(function ($col) {
            return "coalesce({$col}, '')";
        }, $columns);

        $tsVector = "to_tsvector('english', " . implode(" || ' ' || ", $tsVectorParts) . ")";

        $tsQuery = $this->buildTsQuery($term);

        return $query->whereRaw("{$tsVector} @@ to_tsquery('english', ?)", [$tsQuery]);
    }
}
