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
            $this->constructQuestionAttempt(1, 'multichoice'),
            $this->constructQuestionAttempt(2, 'calculated'),
            $this->constructQuestionAttempt(3, 'calculatedmulti'),
            $this->constructQuestionAttempt(4, 'calculatedsimple'),
            $this->constructQuestionAttempt(5, 'randomsamatch'),
            $this->constructQuestionAttempt(6, 'match'),
            $this->constructQuestionAttempt(7, 'shortanswer'),
            $this->constructQuestionAttempt(8, 'somecustomquestiontypethatsnotstandardinmoodle'),
            $this->constructQuestionAttempt(9, 'someothertypewithnoanswers')
        ];
    }

    private function constructQuestionAttempt($index, $qtype) {
         $questionAttempt = (object) [
            'id' => 1,
            'questionid' => 1,
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


        $notchoicetypes = [
            'numerical',
            'calculated',
            'calculatedmulti',
            'calculatedsimple',
            'shortanswer'
        ];

        $matchtypes = [
            'randomsamatch',
            'match'
        ];

        if (in_array($question->qtype, $matchtypes)) {
             $questionAttempt->responsesummary = 'test question -> test answer; test question 2 -> test answer 2';
             $questionAttempt->rightanswer = 'test question -> test answer; test question 2 -> test answer 2';
        } else if (!is_null($question->answers) && ($question->answers !== []) && !in_array($question->qtype, $notchoicetypes)) {
           $questionAttempt->responsesummary = 'test answer; test answer 2';
             $questionAttempt->rightanswer = 'test answer; test answer 2';
        }

        return $questionAttempt;
    }

    private function constructQuestions() {
        return [
            $this->constructQuestion(1, 'multichoice'),
            $this->constructQuestion(2, 'calculated'),
            $this->constructQuestion(3, 'calculatedmulti'),
            $this->constructQuestion(4, 'calculatedsimple'),
            $this->constructQuestion(5, 'randomsamatch'),
            $this->constructQuestion(6, 'match'),
            $this->constructQuestion(7, 'shortanswer'),
            $this->constructQuestion(8, 'somecustomquestiontypethatsnotstandardinmoodle'),
            $this->constructQuestion(9, 'someothertypewithnoanswers'),
            $this->constructQuestion(10, 'shortanswer'),
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
                    'answer' => 'test answer'
                ],
                '1'=> (object)[
                    'id' => '2',
                    'answer' => 'test answer 2'
                ],
                '2'=> (object)[
                    'id' => '3',
                    'answer' => 'wrong test answer'
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
                        'tolerance' => '1'
                    ],
                    '2'=> (object)[
                        'id' => '2',
                        'answer' => '10',
                        'tolerance' => '0'
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
            unset($question->answers)
        }

        if ($index == 10) {
            $question->questiontext = 'test question 2';
            $question->answers = [
                '1'=> (object)[
                    'id' => '1',
                    'answer' => 'test answer 2'
                ]
            ]
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
        $this->assertAttempt($input['attempt'], $output);
        $this->assertQuestion($input['questions'], $output);
    }

    protected function assertAttempt($input, $output) {
        parent::assertAttempt($input, $output);
        $this->assertQuestionAttempt($input->questions, $output);
    }

    protected function assertQuestionAttempt($input, $output) {
        $this->assertEquals((float) $input[0]->maxmark, $output['attempt_score_max']);
        $this->assertEquals((float) $input[0]->steps[1]->fraction, $output['attempt_score_scaled']);
        $this->assertEquals((float) $input[0]->maxmark, $output['attempt_score_max']);
        $this->assertEquals('moodle_quiz_question_answer_1', $output['attempt_response']);
        $this->assertEquals('moodle_quiz_question_answer_1', $output['interaction_correct_responses'][0]);
    }

    protected function assertQuestion($input, $output) {
        $this->assertEquals($input[0]->name, $output['question_name']);
        $this->assertEquals($input[0]->questiontext, $output['question_description']);
        $this->assertEquals($input[0]->answers['2']->answer, $output['interaction_choices']['moodle_quiz_question_answer_2']);
    }
}
