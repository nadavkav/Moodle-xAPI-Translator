<?php namespace MXTranslator\Tests;
use \MXTranslator\Events\ScormEvent as Event;

class ScormEventTest extends ModuleViewedTest {
    protected static $recipe_name = 'scorm_event';

    /**
     * Sets up the tests.
     * @override TestCase
     */
    public function setup() {
        $this->event = new Event();
    }

    protected function constructInput() {
        return array_merge(parent::constructInput(), [
            'scorm_scoes' => $this->constructScormScoes(),
        ]);
    }

    protected function constructScormScoes() {
        return (object)[
            'id' => 1,
            'scorm' => 1,
            'scormtype' => 'sco',
            'title' => 'Sco title'
        ];
    }
}