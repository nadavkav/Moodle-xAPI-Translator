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
            '01' => $this->constructQuestionAttempt('01', 'multichoice'),
            '02' => $this->constructQuestionAttempt('02', 'calculated'),
            '03' => $this->constructQuestionAttempt('03', 'calculatedmulti'),
            '04' => $this->constructQuestionAttempt('04', 'calculatedsimple'),
            '05' => $this->constructQuestionAttempt('05', 'randomsamatch'),
            '06' => $this->constructQuestionAttempt('06', 'match'),
            '07' => $this->constructQuestionAttempt('07', 'shortanswer'),
            '08' => $this->constructQuestionAttempt('08', 'somecustomquestiontypethatsnotstandardinmoodle'),
            '09' => $this->constructQuestionAttempt('09', 'someothertypewithnoanswers'),
            '10' => $this->constructQuestionAttempt('10', 'shortanswer'),
            '11' => $this->constructQuestionAttempt('11', 'truefalse')
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
            'rightanswer' => 'test answer'
        ];

        $choicetypes = [
            'multichoice',
            'truefalse'
        ];

        $matchtypes = [
            'randomsamatch',
            'match'
        ];

        if (in_array($qtype, $matchtypes)) {
            $questionAttempt->responsesummary = 'test question -> test answer; test question 2 -> test answer 2';
            $questionAttempt->rightanswer = 'test question -> test answer; test question 2 -> test answer 2';
        } else if (in_array($qtype, $choicetypes)) {
            $questionAttempt->responsesummary = 'test answer; test answer 2';
            $questionAttempt->rightanswer = 'test answer; test answer 2';
        } else if ($qtype == 'truefalse') {
            $questionAttempt->responsesummary = 'True';
            $questionAttempt->rightanswer = 'True';
        }

        return $questionAttempt;
    }

    private function constructQuestions() {
        return [
            '01' => $this->constructQuestion('01', 'multichoice'),
            '02' => $this->constructQuestion('02', 'calculated'),
            '03' => $this->constructQuestion('03', 'calculatedmulti'),
            '04' => $this->constructQuestion('04', 'calculatedsimple'),
            '05' => $this->constructQuestion('05', 'randomsamatch'),
            '06' => $this->constructQuestion('06', 'match'),
            '07' => $this->constructQuestion('07', 'shortanswer'),
            '08' => $this->constructQuestion('08', 'somecustomquestiontypethatsnotstandardinmoodle'),
            '09' => $this->constructQuestion('09', 'someothertypewithnoanswers'),
            '10' => $this->constructQuestion('10', 'shortanswer'),
            '11' => $this->constructQuestion('11', 'truefalse')
        ];
    }

    private function constructQuestion($index, $qtype) {
        $question = (object) [
            'id' => $index,
            'name' => 'test question {$index}',
            'questiontext' => 'test question',
            'url' => 'http://localhost/moodle/question/question.php?id='.$index,
            'answers' => [
                '1'=> (object)[
                    'id' => '1',
                    'answer' => 'test answer',
                    'fraction' => '0.50'
                ],
                '1'=> (object)[
                    'id' => '2',
                    'answer' => 'test answer 2',
                    'fraction' => '0.50'
                ],
                '2'=> (object)[
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
        } else if ($question->qtype == 'shortanswer') {
            $question->shortanswer = (object)[
                'options' => (object)[
                    'usecase' => '0'
                ]
            ];
        } else if ($question->qtype == 'someothertypewithnoanswers') {
            unset($question->answers);
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
        //length of output is 3.
        $this->assertEquals(3 , count($output));
    }

    protected function assertOutput($input, $output) {
        parent::assertOutput($input, $output);
        $questionindex = substr($output['question_name'], 14, 2);
        $this->assertAttempt($input['attempt'][$questionindex], $output);
        $this->assertQuestion($input['questions'][$questionindex], $output);

    }

    protected function assertAttempt($input, $output) {
        parent::assertAttempt($input, $output);
        $this->assertQuestionAttempt($input->questions, $output);
    }

    protected function assertQuestionAttempt($input, $output) {
        $this->assertEquals((float) $input->maxmark, $output['attempt_score_max']);
        $this->assertEquals(0, $output['attempt_score_min']);
        $this->assertEquals((float) $input->steps[1]->fraction, $output['attempt_score_scaled']);
        $this->assertEquals(((float) $input->maxmark) * ((float) $input->steps[1]->fraction), $output['attempt_score_raw']);
        $this->assertEquals($input->responsesummary, $output['attempt_response']);
        $this->assertEquals(true, $output['attempt_completed']);
        $this->assertEquals(true, $output['attempt_success']);
        $this->assertEquals($input->rightanswer, $output['interaction_correct_responses'][0]);
    }

    protected function assertQuestion($input, $output) {
        $this->assertEquals($input->name, $output['question_name']);
        $this->assertEquals($input->questiontext, $output['question_description']);
        $this->assertEquals($input->url, $output['question_url']);
        $this->assertEquals($input->qtype, $output['interaction_type']);
        $this->assertEquals($input->answers['2']->answer, $output['interaction_choices']['moodle_quiz_question_answer_2']);
    }
}
