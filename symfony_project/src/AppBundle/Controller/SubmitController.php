// src/AppBundle/Controller/SubmitController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class SubmitController extends Controller
{
    /**
     * @Route("/submit/submit")
     */
    public function submit()
    {
        return $this->render('submit/submit.html.twig);
    }
}
