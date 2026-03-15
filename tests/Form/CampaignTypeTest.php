<?php

namespace App\Tests\Form;

use App\Entity\Campaign;
use App\Form\CampaignType;
use Symfony\Component\Form\Test\TypeTestCase;

class CampaignTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'title' => 'Test Campaign',
            'description' => 'Test Description',
            'financialGoal' => 1000.00,
            'totalInvestment' => 500.00,
            'roiPercentage' => 10.00,
        ];

        $model = new Campaign();
        // $model will be modified by the form submission
        $form = $this->factory->create(CampaignType::class, $model);

        $expected = new Campaign();
        $expected->setTitle('Test Campaign');
        $expected->setDescription('Test Description');
        $expected->setFinancialGoal('1000.00');
        $expected->setTotalInvestment('500.00');
        $expected->setRoiPercentage('10.00');

        // submit the data to the form directly
        $form->submit($formData);

        // This check ensures there are no transformation failures
        $this->assertTrue($form->isSynchronized());

        // check that $model was modified as expected
        $this->assertEquals($expected->getTitle(), $model->getTitle());
        $this->assertEquals($expected->getDescription(), $model->getDescription());
        // Doctrine decimals are usually strings in the entity
        $this->assertEquals((float)$expected->getFinancialGoal(), (float)$model->getFinancialGoal());
        $this->assertEquals((float)$expected->getTotalInvestment(), (float)$model->getTotalInvestment());
        $this->assertEquals((float)$expected->getRoiPercentage(), (float)$model->getRoiPercentage());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
