<?php

namespace Market\Forms;

use Market\Main;
use Market\FormAPI\CustomForm;
use pocketmine\player\Player;

class PriceForm {
    
    private Main $plugin;
    
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }
    
    public function sendForm(Player $player): void {
        $items = $this->plugin->getMarketManager()->getAllItems();
        $itemNames = [];
        $itemIds = [];
        
        foreach ($items as $itemId => $itemData) {
            $name = $itemData["name"] ?? $itemId;
            $itemNames[] = "§e§l{$name} §r§7({$itemId})";
            $itemIds[] = $itemId;
        }
        
        $languageManager = $this->plugin->getLanguageManager();
        
        if (empty($itemNames)) {
            $player->sendMessage($languageManager->get("price.no_items", $player));
            return;
        }
        
        $plugin = $this->plugin;
        $form = new CustomForm(function (Player $player, ?array $response) use ($itemIds, $plugin) {
            if ($response === null) {
                return;
            }
            
            $selectedIndex = (int) $response["item"];
            
            $langManager = $plugin->getLanguageManager();
            
            if (!isset($itemIds[$selectedIndex])) {
                $player->sendMessage($langManager->get("price.invalid_selection", $player));
                return;
            }
            
            $itemId = $itemIds[$selectedIndex];
            $buyPrice = $plugin->getMarketManager()->getPrice($itemId, "buy");
            $sellPrice = $plugin->getMarketManager()->getPrice($itemId, "sell");
            $displayName = $plugin->getMarketManager()->getItemDisplayName($itemId);
            
            $config = $plugin->getMarketConfig();
            $settings = $config->get("settings", []);
            $currency = $settings["currency"] ?? "Para";
            
            $name = $displayName ?? $itemId;
            
            $player->sendMessage("§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $player->sendMessage($langManager->get("price.form_title", $player, ["name" => $name]));
            $player->sendMessage("§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
            
            if ($buyPrice !== null) {
                $player->sendMessage($langManager->get("price.form_buy", $player, ["buy_price" => $buyPrice, "currency" => $currency]));
            }
            
            if ($sellPrice !== null) {
                $player->sendMessage($langManager->get("price.form_sell", $player, ["sell_price" => $sellPrice, "currency" => $currency]));
            }
            
            $player->sendMessage("\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        });
        
        $form->setTitle($languageManager->get("price.title", $player));
        $form->addLabel($languageManager->get("price.content", $player));
        $form->addDropdown($languageManager->get("price.item_dropdown", $player), $itemNames, 0, "item");
        
        $player->sendForm($form);
    }
}
