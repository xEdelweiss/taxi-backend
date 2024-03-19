<?php

namespace PHPSTORM_META {
    override(\Symfony\Bundle\FrameworkBundle\Controller\AbstractController::getUser(), map([
        '' => \App\Entity\User::class,
    ]));
}
