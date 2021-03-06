<?php

namespace Tests\Feature;

use Tests\Feature\Traits\CreateEntitiesTrait;
use Tests\TestCase;

class JobApplyTest extends TestCase
{
    use CreateEntitiesTrait;

    public function testApply()
    {
        $jobId = $this->createJob('apply.job@client.test');
        $response = $this->postJson('/api/freelancers/apply-to-job', [
            'jobId' => $jobId,
            'freelancerId' => $this->createFreelancer('apply.job@freelancer.test'),
            'coverLetter' => 'cover letter',
        ]);

        $response->assertOk();

        $checkResponse = $this->get('/api/jobs-with-proposals/' . $jobId);

        $checkResponse->assertOk();

        $checkResponse->assertJsonStructure([
            'proposals'
        ]);

        $this->assertEquals(1, count($checkResponse->getData()->proposals));
    }

    public function testValidation()
    {
        $response = $this->postJson('/api/freelancers/apply-to-job', [
            'jobId' => 'not a number',
            'freelancerId' => 'not a number',
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => ['jobId', 'freelancerId'],
        ]);
    }

    public function testSameFreelancer()
    {
        $job = $this->createJob('apply.job.same@client.test');
        $freelancer = $this->createFreelancer('apply.job.same@freelancer.test');

        $response = $this->postJson('/api/freelancers/apply-to-job', [
            'jobId' => $job,
            'freelancerId' => $freelancer,
            'coverLetter' => 'cover letter',
        ]);

        $response->assertOk();

        $response = $this->postJson('/api/freelancers/apply-to-job', [
            'jobId' => $job,
            'freelancerId' => $freelancer,
            'coverLetter' => 'another cover letter',
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure(['error']);
    }
}
