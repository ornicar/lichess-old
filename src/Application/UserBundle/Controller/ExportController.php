<?php

namespace Application\UserBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Lichess\ChartBundle\Chart\UsersEloChart;
use Lichess\ChartBundle\Chart\GameEndChart;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExportController extends ContainerAware
{
    public function exportAction($username)
    {
        $user = $this->container->get('fos_user.user_manager')->findUserByUsername($username);
        if (!$user) {
            throw new NotFoundHttpException('No user with username '.$username);
        }
        $data = $this->container->get('lichess_user.game_exporter')->getData($user);
        $file = tempnam('/tmp', 'lichess_export_');
        $writer = new \EasyCSV\Writer($file);
        $writer->writeFromArray($data);
        $filename = sprintf('%s_lichess_games_%s.csv', $user->getUsername(), date_create()->format('Y-m-d'));
        $data = file_get_contents($file);
        unlink($file);

        return new Response($data, 200, array(
            'content-type' => 'text/csv',
            'content-description' => 'File Transfer',
            'content-disposition' => sprintf('attachment; filename="%s";', $filename),
            'content-transfer-encoding' => 'binary'
        ));
    }
}
