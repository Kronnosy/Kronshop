<?php

namespace Market\Forms;

use Market\Main;
use Market\FormAPI\SimpleForm;
use Market\Forms\SettingsForm;
use pocketmine\player\Player;

class MarketMenuForm {
    
    private Main $plugin;
    
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }
    
    public function sendForm(Player $player): void {
        $plugin = $this->plugin;
        $form = new SimpleForm(function (Player $player, $data) use ($plugin) {
            if ($data === null) {
                return;
            }
            
            switch ($data) {
                case "buy":
                    // Satın Alma
                    $buyForm = new BuyForm($plugin);
                    $buyForm->sendForm($player);
                    break;
                case "sell":
                    // Satış
                    $sellForm = new SellForm($plugin);
                    $sellForm->sendForm($player);
                    break;
                case "list":
                    // Liste
                    $listForm = new ListForm($plugin);
                    $listForm->sendForm($player);
                    break;
                case "price":
                    // Fiyat Sorgula
                    $priceForm = new PriceForm($plugin);
                    $priceForm->sendForm($player);
                    break;
                case "settings":
                    // Ayarlar
                    $settingsForm = new SettingsForm($plugin);
                    $settingsForm->sendForm($player);
                    break;
            }
        });
        
        $languageManager = $plugin->getLanguageManager();
        
        $form->setTitle($languageManager->get("menu.title", $player));
        $form->setContent($languageManager->get("menu.content", $player));
        $form->addButton($languageManager->get("menu.buy_button", $player), SimpleForm::IMAGE_TYPE_PATH, "textures/items/diamond", "buy");
        $form->addButton($languageManager->get("menu.sell_button", $player), SimpleForm::IMAGE_TYPE_PATH, "textures/items/gold_ingot", "sell");
        $form->addButton($languageManager->get("menu.list_button", $player), SimpleForm::IMAGE_TYPE_PATH, "textures/items/book_normal", "list");
        $form->addButton($languageManager->get("menu.price_button", $player), SimpleForm::IMAGE_TYPE_PATH, "textures/items/paper", "price");
        $form->addButton($languageManager->get("menu.settings_button", $player), SimpleForm::IMAGE_TYPE_PATH, "textures/blocks/barrier", "settings");
        
        $player->sendForm($form);
    }
}
