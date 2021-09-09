<?php

namespace System\Helper;
class Locales extends \Lime\Helper {

    protected array $locales = [];

    protected function initialize() {

        $this->locales = $this->app['debug'] ? $this->cache() : $this->app->helper('cache')->read('app.locales', function() {
            return $this->cache();
        });
    }

    public function locales(bool $assoc = false): array {

        if ($assoc) {
            return $this->locales;
        }

        $locales = [];

        foreach ($this->locales as $locale) {

            $locales[] = [
                'i18n' => $locale['i18n'],
                'name' => $locale['name'],
            ];
        }

        return $locales;
    }

    public function applyLocales($obj, $locale = 'default') {

        static $locales;

        if (!is_array($obj)) {
            return $obj;
        }

        if (null === $locales) {
            $locales = array_keys($this->locales(true));
        }

        $apply = function($obj) use($locales, $locale) {

            if (!is_array($obj)) return $obj;

            $keys = array_filter(array_keys($obj), function($key) use($locales) {

                foreach ($locales as $l) {
                    if (preg_match("/_{$l}$/", $key)) return false;
                }

                return true;
            });

            foreach ($keys as $key) {

                foreach ($locales as $l) {

                    if (isset($obj["{$key}_{$l}"]) && $obj["{$key}_{$l}"] !== '') {

                        if ($l == $locale) {

                            $obj[$key] = $obj["{$key}_{$l}"];

                            if (isset($obj["{$key}_{$l}_slug"])) {
                                $obj["{$key}_slug"] = $obj["{$key}_{$l}_slug"];
                            }
                        }

                    }

                    unset($obj["{$key}_{$l}"]);
                }

                if (isset($obj[$key]) && is_array($obj[$key])) {
                    $obj[$key] = $this->applyLocales($obj[$key], $locale);
                }
            }

            return $obj;
        };

        if (isset($obj[0])) {
            $obj = array_map($apply, $obj);
        } else {
            $obj = $apply($obj);
        }

        return $obj;
    }

    public function cache(): array {

        $cache = [
            'default' => [
                'i18n' => 'default',
                'name' => 'Default'
            ]
        ];

        $locales = $this->app->dataStorage->find('system/locales', [
            'sort' => ['name' => 1]
        ])->toArray();

        foreach ($locales as $locale) {
            $cache[$locale['i18n']] = $locale;
        }

        $this->app->helper('cache')->write('app.locales', $cache);

        return $cache;
    }
}