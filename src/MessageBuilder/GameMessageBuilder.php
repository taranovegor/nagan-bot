<?php
/**
 * (c) Taranov Egor <dev@taranovegor.com>
 */

namespace App\MessageBuilder;

use App\Telegram\Command\JoinCommand;
use App\Entity\Game\Game;
use App\Entity\Game\Gunslinger;
use App\Entity\Game\ShotHimself;
use App\Model\Message;
use App\Model\Username;
use Generator;

/**
 * Class GameMessageBuilder
 */
class GameMessageBuilder extends AbstractTranslatableMessageBuilder
{
    /**
     * @return Message
     */
    public function buildCreate(): Message
    {
        return $this->message
            ->clear()
            ->addLine($this->translator->trans('game.invite', [
                'variation' => mt_rand(1, 3),
            ]))
            ->addLine($this->translator->trans('game.invite.join', [
                '%command' => JoinCommand::COMMAND,
            ]))
        ;
    }

    /**
     * @return Message
     */
    public function buildJoin(): Message
    {
        return $this->message
            ->clear()
            ->addLine($this->translator->trans('game.join', [
                'variation' => mt_rand(1, 7),
            ]))
        ;
    }

    /**
     * @return Generator
     */
    public function buildPlay(): Generator
    {
        $this->message->clear();

        $variation = mt_rand(1, 2);
        for ($step = 0; $step <= 1; $step++) {
            yield $step => $this->message->clear()->add(
                $this->translator->trans('game.play', [
                    'variation' => $variation,
                    'step' => $step,
                ])
            );
        }
    }

    /**
     * @param Gunslinger $shotHimself
     *
     * @return Message
     */
    public function buildShotHimself(Gunslinger $shotHimself): Message
    {
        return $this->message
            ->clear()
            ->add($this->translator->trans('game.shot_himself', [
                '%gunslinger' => Username::fromUser($shotHimself->getUser())->toString(),
            ]))
        ;
    }

    /**
     * @param Game $gameTable
     *
     * @return Message
     */
    public function buildJoined(Game $gameTable): Message
    {
        $this->message
            ->clear()
            ->addLine($this->translator->trans('game.joined_list.title', [
                '%date' => $gameTable->getCreatedAt()->format('d.m.Y'),
            ]))
        ;

        foreach ($gameTable->getGunslingers() as $num => $gunslinger) {
            $this->message->add($this->translator->trans('game.joined_list.item', [
                '%num' => $num + 1,
                '%gunslinger' => Username::fromUser($gunslinger->getUser())->toString(),
            ]))->addSpace();

            if ($gameTable->getOwner()->isSame($gunslinger->getUser())) {
                $this->message->add(
                    $this->translator->trans('game.joined_list.item_info.owner')
                )->addSpace();
            }

            if ($gameTable->isPlayed() && $gunslinger->isShotHimself()) {
                $this->message->add(
                    $this->translator->trans('game.joined_list.item_info.shot_himself')
                )->addSpace();
            }

            $this->message->nextLine();
        }

        return $this->message;
    }
}
