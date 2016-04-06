<?php namespace MXTranslator\Events;

class FeedbackSubmitted extends ModuleViewed {
    /**
     * Reads data for an event.
     * @param [String => Mixed] $opts
     * @return [String => Mixed]
     * @override AttemtStarted
     */
    public function read(array $opts) {

        $feedback = $this->parseFeedback($opts);

        //$parsedQuestions = [{"options":[{"description":"Not selected"},{"description":"agree "},{"description":"disagree "},{"description":"whateverz"}],"score":{"max":null,"raw":null},"response":"2"},{"options":[{"description":"Not selected"},{"description":"foo","value":"4"},{"description":"bar","value":"5"}],"score":{"max":"5","raw":0},"response":"0"},{"options":[],"score":{"max":null,"raw":null},"response":null}]}]

        return [array_merge(parent::read($opts)[0], [
            'recipe' => 'attempt_completed',
            'attempt_url' => $opts['attempt']->url,
            'attempt_type' => static::$xapi_type.$opts['attempt']->type,
            'attempt_ext' => $opts['attempt'],
            'attempt_ext_key' => 'http://lrs.learninglocker.net/define/extensions/moodle_feedback_attempt',
            'attempt_name' => $opts['attempt']->name,
            'attempt_score_raw' => $feedback->score->raw,
            'attempt_score_min' => $feedback->score->min,
            'attempt_score_max' => $feedback->score->max,
            'attempt_score_scaled' => $feedback->score->scaled,
            'attempt_success' => null,
            'attempt_completed' => true,
            'attempt_duration' => null,
        ])];
    }

    /**
     * Converts a outputs feedback question and result data in a more manageable format
     * @param [Array => Mixed] $opts
     * @return [PHPObj => Mixed]
     */
    protected function parseFeedback($opts){
        $parsedQuestions = array();
        $scoreMax = 0;
        $scoreRaw = 0;

        foreach ($opts['questions'] as $item => $question) {
            // Find the response to the current question
            $currentResponse = null;
            foreach ($opts['attempt']->responses as $responseId => $response) {
                if ($response->item == $item) {
                    $currentResponse = $response;
                }
            }

            // Parse the current question
            $parsedQuestion = (object)[
                'options' => $this->parseQuestionPresentation($question->presentation, $question->typ),
                'score' => (object) [
                    'max' => 0,
                    'raw' => 0
                ],
                'response' => null
            ];

            // Sometimes $currentResponse->value contains the actual response, so default to that. 
            $parsedQuestion->response = $currentResponse->value;

            // Add scores and response
            foreach ($parsedQuestion->options as $optionIndex => $option) {
                if (isset($option->value) && $option->value > $parsedQuestion->score->max) {
                    $parsedQuestion->score->max = $option->value;
                }

                // Find the option the learner selected
                if ($optionIndex == $currentResponse->value){
                    // Sometimes $currentResponse->value contains the id of an option, so look up the description
                    $parsedQuestion->response = $option->description;
                    if (isset($option->value)) {
                        $parsedQuestion->score->raw = $option->value;
                    }
                }
            }

            $scoreMax += $parsedQuestion->score->max;
            $scoreRaw += $parsedQuestion->score->raw;

            if ($parsedQuestion->score->max == 0) {
                $parsedQuestion->score->max = null;
                $parsedQuestion->score->raw = null;
            }

            array_push(
                $parsedQuestions, 
                $parsedQuestion
            );
        }

        $scoreMin = null;
        $scoreScaled = null;
        if ($scoreMax == 0){
            $scoreMax = null;
            $scoreRaw = null;
        }
        else {
            $scoreScaled = $scoreRaw / $scoreMax;
            $scoreMin = 0;
        }

        return (object)[
            'questions' => $parsedQuestions,
            'score' => (object) [
                'max' => $scoreMax,
                'raw' => $scoreRaw,
                'min' => $scoreMin,
                'scaled' => $scoreScaled
            ] 
        ];
    }

    /**
     * Converts a feedback item "presentation" string into an array
     * @param [String => Mixed] $presentation
     * @param [String => Mixed] $type
     * @return [Array => Mixed]
     */
    protected function parseQuestionPresentation ($presentation, $type){

        // Text areas don't have options or scores
        if ($type == 'textarea') {
            return array();
        }

        // Strip out the junk.
        $presentation = str_replace('r>>>>>', '', $presentation);
        $presentation = trim(preg_replace('/\s+/', ' ', $presentation));
        $presentation = strip_tags($presentation);

        $options = explode('|', $presentation);
        $return = array((object)[
            'description' => 'Not selected'
        ]);

        foreach ($options as $index => $option) {
            switch ($type) {
                case 'multichoice':
                    array_push($return, (object)[
                        'description' => $option
                    ]);
                    break;
                case 'multichoicerated':
                    $optionArr = explode('#### ', $option);
                    array_push($return, (object)[
                        'description' => $optionArr[1],
                        'value' => $optionArr[0]
                    ]);
                    break;
                default:
                    // Unsupported type. 
                    return array();
                    break;
            }
        }

        return $return;
    }

}