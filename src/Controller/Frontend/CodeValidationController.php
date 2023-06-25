<?php

declare(strict_types=1);

/*
 * This file is part of the Ferienpass package.
 *
 * (c) Richard Henkenjohann <richard@ferienpass.online>
 *
 * For more information visit the project website <https://ferienpass.online>
 * or the documentation under <https://docs.ferienpass.online>.
 */

namespace Ferienpass\CoreBundle\Controller\Frontend;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CodeValidationController
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * Validate the code against the ones present in the database. Return a boolean status about the code being valid
     * or not.
     *
     * @param Request $request the current request
     */
    #[Route(path: '/ferienpass-code-validate')]
    public function __invoke(Request $request): Response
    {
        if (!$request->isMethod('post')) {
            return new Response('Request not allowed', Response::HTTP_PRECONDITION_FAILED);
        }

        // FIXME use Rate Limiter (allowed tries = 5)

        $data = [];

        $code = $request->request->get('code');
        $attributeId = $request->request->get('att_id');
        $itemId = $request->request->get('item_id');

        $expr = $this->connection->createExpressionBuilder();

        $statement = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('tl_ferienpass_code')
            ->where('code=:code')
            ->andWhere(
                $expr->or('activated=0', $expr->and('activated<>0', 'item_id=:item', 'att_id=:attr'))
            )
            ->setParameter('code', $code)
            ->setParameter('item', $itemId)
            ->setParameter('attr', $attributeId)
            ->executeQuery();

        $success = (bool) $statement->fetchOne();

        $data['code'] = $code;
        $data['success'] = $success;

        return new JsonResponse($data);
    }
}
