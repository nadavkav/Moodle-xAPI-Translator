<?php namespace MXTranslator\Tests;
use \MXTranslator\Events\QuestionSubmitted as Event;

class QuestionSubmittedTest extends AttemptStartedTest {
    protected static $recipe_name = 'attempt_question_completed';

    /**
     * Sets up the tests.
     * @override TestCase
     */
    public function setup() {
        $this->event = new Event($this->repo);
    }

    protected function constructInput() {
        $input = array_merge(parent::constructInput(), [
            'questions' => $this->constructQuestions()
        ]);
        $input['attempt']->questions = $this->constructQuestionAttempts();

        return $input;
    }

    private function constructQuestionAttempts() {
        return [
            $this->constructQuestionAttempt(0, 'truefalse'),
            $this->constructQuestionAttempt(1, 'multichoice'),
            $this->constructQuestionAttempt(2, 'calculated'),
            $this->constructQuestionAttempt(3, 'calculatedmulti'),
            $this->constructQuestionAttempt(4, 'calculatedsimple'),
            $this->constructQuestionAttempt(5, 'randomsamatch'),
            $this->constructQuestionAttempt(6, 'match'),
            $this->constructQuestionAttempt(7, 'shortanswer'),
            $this->constructQuestionAttempt(8, 'somecustomquestiontypethatsnotstandardinmoodle'),
            $this->constructQuestionAttempt(9, 'someothertypewithnoanswers'),
            $this->constructQuestionAttempt(10, 'shortanswer')
        ];
    }

    private function constructQuestionAttempt($index, $qtype) {
         $questionAttempt = (object) [
            'id' => $index,
            'questionid' => $index,
            'maxmark' => '5.0000000',
            'steps' => [
                (object)[
                    'sequencenumber' => 1,
                    'state' => 'todo',
                    'timecreated' => '1433946000',
                    'fraction' => null
                ],
                (object)[
                    'sequencenumber' => 2,
                    'state' => 'gradedright',
                    'timecreated' => '1433946701',
                    'fraction' => '1.0000000'
                ],
            ],
            'responsesummary' => 'test answer',
            'rightanswer' => 'test answer',
            'variant' => '1'
        ];

        $choicetypes = [
            'multichoice',
            'somecustomquestiontypethatsnotstandardinmoodle'
        ];

        $matchtypes = [
            'randomsamatch',
            'match'
        ];

        $numerictypes = [
            'numerical',
            'calculated',
            'calculatedmulti',
            'calculatedsimple'
        ];

        if (in_array($qtype, $matchtypes)) {
            $questionAttempt->responsesummary = 'test question -> test answer; test question 2 -> test answer 2';
            $questionAttempt->rightanswer = 'test question -> test answer; test question 2 -> test answer 2';
        } else if (in_array($qtype, $choicetypes)) {
            $questionAttempt->responsesummary = 'test answer; test answer 2';
            $questionAttempt->rightanswer = 'test answer; test answer 2';
        } else if (in_array($qtype, $numerictypes)) {
            $questionAttempt->rightanswer = '5';
        } else if ($qtype == 'truefalse') {
            $questionAttempt->responsesummary = 'True';
            $questionAttempt->rightanswer = 'True';
        }

        return $questionAttempt;
    }

    private function constructQuestions() {
        return [
            $this->constructQuestion('00', 'truefalse'),
            $this->constructQuestion('01', 'multichoice'),
            $this->constructQuestion('02', 'calculated'),
            $this->constructQuestion('03', 'calculatedmulti'),
            $this->constructQuestion('04', 'calculatedsimple'),
            $this->constructQuestion('05', 'randomsamatch'),
            $this->constructQuestion('06', 'match'),
            $this->constructQuestion('07', 'shortanswer'),
            $this->constructQuestion('08', 'somecustomquestiontypethatsnotstandardinmoodle'),
            $this->constructQuestion('09', 'someothertypewithnoanswers'),
            $this->constructQuestion('10', 'shortanswer')
        ];
    }

    private function constructQuestion($index, $qtype) {
        $question = (object) [
            'id' => $index,
            'name' => 'test question '.$index,
            'questiontext' => 'test question',
            'url' => 'http://localhost/moodle/question/question.php?id='.$index,
            'answers' => [
                '1'=> (object)[
                    'id' => '1',
                    'answer' => 'test answer',
                    'fraction' => '0.50'
                ],
                '2'=> (object)[
                    'id' => '2',
                    'answer' => 'test answer 2',
                    'fraction' => '0.50'
                ],
                '3'=> (object)[
                    'id' => '3',
                    'answer' => 'wrong test answer',
                    'fraction' => '0.00'
                ]
            ],
            'qtype' => $qtype
        ];

        if ($question->qtype == 'numerical') {
            $question->numerical = (object)[
                'answers' => [
                    '1'=> (object)[
                        'id' => '1',
                        'answer' => '5',
                        'tolerance' => '1',
                        'fraction' => '1.00'
                    ],
                    '2'=> (object)[
                        'id' => '2',
                        'answer' => '10',
                        'tolerance' => '0',
                        'fraction' => '0.00'
                    ]
                ]
            ];
        } else if ($question->qtype == 'match') {
            $question->match = (object)[
                'subquestions' => [
                    '1'=> (object)[
                        'id' => '1',
                        'questiontext' => '<p>test question</p>',
                        'answertext' => '<p>test answer</p>'
                    ],
                    '2'=> (object)[
                        'id' => '2',
                        'questiontext' => '<p>test question 2</p>',
                        'answertext' => '<p>test answer 2</p>'
                    ]
                ]
            ];
        } else if (strpos($question->qtype, 'calculated') === 0) {
            $question->calculated = (object)[
                'answers' => [
                    '1'=> (object)[
                        'id' => '1',
                        'answer' => '5',
                        'tolerance' => '1'
                    ],
                    '2'=> (object)[
                        'id' => '2',
                        'answer' => '10',
                        'tolerance' => '0'
                    ]
                ]
            ];
            $question->answers['1']->fraction = '1.00';
            $question->answers['2']->fraction = '1.00';
        } else if ($question->qtype == 'shortanswer') {
            $question->shortanswer = (object)[
                'options' => (object)[
                    'usecase' => '0'
                ]
            ];
        } else if ($question->qtype == 'someothertypewithnoanswers') {
            $question->answers = [];
        } else if ($question->qtype == 'truefalse') {
            $question->answers = [
                '1'=> (object)[
                    'id' => '1',
                    'answer' => 'True',
                    'fraction' => '1.00'
                ],
                '2'=> (object)[
                    'id' => '2',
                    'answer' => 'False',
                    'fraction' => '0.00'
                ]
            ];
        }

        if ($index == '10') {
            $question->questiontext = 'test question 2';
            $question->answers = [
                '1'=> (object)[
                    'id' => '1',
                    'answer' => 'test answer 2',
                    'fraction' => '1.00'
                ]
            ];
        }
        return $question;
    }

    protected function assertOutputs($input, $output) {
        //output is an associative array
        $this->assertEquals(0, count(array_filter(array_keys($output), 'is_string')));
        $this->assertEquals(count($input['questions']) , count($output));
    }

    protected function assertOutput($input, $output) {
        parent::assertOutput($input, $output);
        $questionindex = intval(substr($output['question_name'], 14, 2), 10);
        $this->assertAttempt($input['attempt'], $output);
        $this->assertQuestion($input['questions'][$questionindex], $output);
        $this->assertQuestionAttempt($input['attempt']->questions[$questionindex], $output, $input['questions'][$questionindex]);
    }

    protected function assertAttempt($input, $output) {
        parent::assertAttempt($input, $output);
    }

    protected function assertQuestionAttempt($input, $output, $question) {
        $this->assertEquals((float) $input->maxmark, $output['attempt_score_max']);
        $this->assertEquals(0, $output['attempt_score_min']);
        $this->assertEquals((float) $input->steps[1]->fraction, $output['attempt_score_scaled']);
        $this->assertEquals(((float) $input->maxmark) * ((float) $input->steps[1]->fraction), $output['attempt_score_raw']);
        $this->assertEquals(true, $output['attempt_completed']);
        $this->assertEquals(true, $output['attempt_success']);

        $numerictypes = [
            'numerical',
            'calculated',
            'calculatedmulti',
            'calculatedsimple'
        ];

        $matchtypes = [
            'randomsamatch',
            'match'
        ];

        $fillintypes = [
            'shortanswer'
        ];

        $noanswer = [
            'someothertypewithnoanswers'
        ];

        if (in_array($question->qtype, $matchtypes)) {
            $this->assertEquals(
                strip_tags(
                    $question->match->subquestions['1']->questiontext.'[.]'
                    .$question->match->subquestions['1']->answertext.'[,]'
                    .$question->match->subquestions['2']->questiontext.'[.]'
                    .$question->match->subquestions['2']->answertext
                ),
                $output['interaction_correct_responses'][0]
            );
        } else if (in_array($question->qtype, $numerictypes)) {
             $this->assertEquals('4[:]6', $output['interaction_correct_responses'][0]);
        } else if (in_array($question->qtype, $fillintypes)) {
            $this->assertEquals(
                $question->answers['1']->answer, 
                $output['interaction_correct_responses'][0]
            );
            $this->assertEquals(
                $question->answers['2']->answer, 
                $output['interaction_correct_responses'][1]
            );
        } else if ($question->qtype == 'truefalse') {
            $this->assertEquals(
                $question->answers['1']->answer, 
                $output['interaction_correct_responses'][0]
            );
        } else if (!in_array($question->qtype, $noanswer)) {
            // Multichoice
            $this->assertEquals(
                strip_tags(
                    $question->answers['1']->answer.'[,]'
                    .$question->answers['2']->answer
                ),
                $output['interaction_correct_responses'][0]
            );
        } else {
            // Default
            $this->assertEquals($input->rightanswer, $output['interaction_correct_responses'][0]);
        }

        // For the purposes of testing, the response is always correct. Test that the format is right.
        $this->assertEquals($output['interaction_correct_responses'][0], $output['attempt_response']);

    }

    protected function assertQuestion($input, $output) {
        $this->assertEquals($input->name, $output['question_name']);
        $this->assertEquals($input->questiontext, $output['question_description']);
        if (strpos($input->qtype, 'calculated') === 0) {
            $this->assertEquals($input->url.'&variant=1', $output['question_url']);
        } else {
            $this->assertEquals($input->url, $output['question_url']);
        }

        $numerictypes = [
            'numerical',
            'calculated',
            'calculatedmulti',
            'calculatedsimple'
        ];

        $matchtypes = [
            'randomsamatch',
            'match'
        ];

        $fillintypes = [
            'shortanswer'
        ];

        $multitypes = [
            'somecustomquestiontypethatsnotstandardinmoodle',
            'multichoice'
        ];

        if (in_array($input->qtype, $matchtypes)) {
            $this->assertEquals(
                strip_tags($input->match->subquestions['2']->questiontext), 
                $output['interaction_target']['moodle_quiz_question_target_2']
            );
            $this->assertEquals(
                strip_tags($input->match->subquestions['2']->answertext), 
                $output['interaction_source']['moodle_quiz_question_source_2']
            );
            $this->assertEquals('match', $output['interaction_type']);
        } else if (in_array($input->qtype, $multitypes)) {
            $this->assertEquals($input->answers['2']->answer, $output['interaction_choices']['moodle_quiz_question_answer_2']);
            $this->assertEquals('choice', $output['interaction_type']);
        } else if ($input->qtype == 'truefalse') {
            $this->assertEquals('true-false', $output['interaction_type']);
        } else if (in_array($input->qtype, $numerictypes)) {
            $this->assertEquals('numeric', $output['interaction_type']);
        } else if (in_array($input->qtype, $fillintypes)) {
            $this->assertEquals('fill-in', $output['interaction_type']);
        } else {
            $this->assertEquals('other', $output['interaction_type']);
        }
    }
}
