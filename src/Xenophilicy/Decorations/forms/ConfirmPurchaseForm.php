<?php
# MADE BY:
#  __    __                                          __        __  __  __
# /  |  /  |                                        /  |      /  |/  |/  |
# $$ |  $$ |  ______   _______    ______    ______  $$ |____  $$/ $$ |$$/   _______  __    __
# $$  \/$$/  /      \ /       \  /      \  /      \ $$      \ /  |$$ |/  | /       |/  |  /  |
#  $$  $$<  /$$$$$$  |$$$$$$$  |/$$$$$$  |/$$$$$$  |$$$$$$$  |$$ |$$ |$$ |/$$$$$$$/ $$ |  $$ |
#   $$$$  \ $$    $$ |$$ |  $$ |$$ |  $$ |$$ |  $$ |$$ |  $$ |$$ |$$ |$$ |$$ |      $$ |  $$ |
#  $$ /$$  |$$$$$$$$/ $$ |  $$ |$$ \__$$ |$$ |__$$ |$$ |  $$ |$$ |$$ |$$ |$$ \_____ $$ \__$$ |
# $$ |  $$ |$$       |$$ |  $$ |$$    $$/ $$    $$/ $$ |  $$ |$$ |$$ |$$ |$$       |$$    $$ |
# $$/   $$/  $$$$$$$/ $$/   $$/  $$$$$$/  $$$$$$$/  $$/   $$/ $$/ $$/ $$/  $$$$$$$/  $$$$$$$ |
#                                         $$ |                                      /  \__$$ |
#                                         $$ |                                      $$    $$/
#                                         $$/                                        $$$$$$/

namespace Xenophilicy\Decorations\forms;

use cooldogedev\BedrockEconomy\libs\cooldogedev\libSQL\context\ClosureContext;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Decorations\decoration\Decoration;
use Xenophilicy\Decorations\Decorations;
use BreathTakinglyBinary\libDynamicForms\Form;
use BreathTakinglyBinary\libDynamicForms\ModalForm;

/**
 * Class ConfirmPurchaseForm
 * @package Xenophilicy\Decorations\forms
 */
class ConfirmPurchaseForm extends ModalForm implements FormConstants {
    
    /** @var Decoration */
    private Decoration $decoration;
    /** @var int */
    private int $amount;
    /** @var int */
    private int $location;
    
    public function __construct(Decoration $decoration, int $amount, int $location, Form $previousForm){
        $this->decoration = $decoration;
        $this->amount = $amount;
        $this->location = $location;
        parent::__construct(self::TITLE, $previousForm);
        $price = $decoration->getPrice() * $this->amount;
        $unit = "$";
        $price = $unit . ($price > 0 ? $price : "FREE");
        $this->setContent(TF::YELLOW . "Please confirm you'd like to buy " . TF::AQUA . $amount . "x " . $decoration->getFormat() . TF::YELLOW . " for " . TF::DARK_GREEN . $price);
        $this->setButton1(TF::GREEN . "Confirm");
        $this->setButton2(TF::RED . "Back");
    }
    
    /**
     * Children classes should implement this method to properly
     * deal with non-null player responses.
     * @param Player $player
     * @param        $data
     */
    public function onResponse(Player $player, $data): void{
        if(!$data){
            $form = $this->getPreviousForm()->getPreviousForm();
        }else{
            if($this->location === 0){
                Decorations::getInstance()->getArchiveManager()->getArchive($player->getName())->addStored($this->decoration->getId(), $this->amount);
                $location = "decoration archive";
            }else{
                $item = $this->decoration->convertToItem($this->amount);
                $player->getInventory()->canAddItem($item) ? $player->getInventory()->addItem($item) : $player->dropItem($item);
                $location = "inventory";
            }
            $price = $this->decoration->getPrice() * $this->amount;
            Decorations::getInstance()->getEconomy()->getAPI()->subtractFromPlayerBalance(
                $player->getName(),
                $price,
                ClosureContext::create(
                    function (bool $wasUpdated): void {
                    },
                )
            );
            $form = new AlertForm(TF::GREEN . "You've added " . TF::AQUA . $this->amount . "x " . $this->decoration->getFormat() . TF::GREEN . " to your $location", new
            MainForm());
        }
        $player->sendForm($form);
    }
}
