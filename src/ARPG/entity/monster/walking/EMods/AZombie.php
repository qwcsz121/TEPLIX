<?php

namespace ARPG\entity\monster\walking\EMods;

use ARPG\entity\monster\WalkingMonster;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\network\protocol\MobEquipmentPacket;
use pocketmine\network\protocol\MobArmorEquipmentPacket;

class AZombie extends WalkingMonster{

 const NETWORK_ID = 32;

 public $width = 0.6;
 public $height = 1.8;
 public $eyeHeight = 1.62;
    
 public function initEntity(){
  parent::initEntity();

  $this->setDamage([ 3, 5, 6, 7]);
 }

 public function getName(){
 
  return "Zombie";
 }
    
 public function spawnTo(Player $player){
  parent::spawnTo($player);

  $pk = new MobEquipmentPacket();
  $pk->eid = $this->getId();
  $pk->item = $this->addEnchant($this->namedtag["Held"]);;
  $pk->slot = 10;
  $pk->selectedSlot = 10;
  $player->dataPacket($pk);
      
  $Armor = new MobArmorEquipmentPacket();
  $Armor->eid = $this->getId();
  $Armor->slots = [
    0 => $this->addEnchant($this->namedtag["Cap"]),
    1 => $this->addEnchant($this->namedtag["Tunic"]),
    2 => $this->addEnchant($this->namedtag["Pants"]),
    3 => $this->addEnchant($this->namedtag["Boots"])
  ];
  $player->dataPacket($Armor);

 }
    
 public function addEnchant($array){
     
  $item = Item::get($array[0]);
     
  if($array[0] <> 0 and $array[2] > 0){
   
   $enchantment = Enchantment::getEnchantment($array[1])->setLevel($array[2]);
   $item->addEnchantment($enchantment);
  }
  
  return $item;
 }
    
 public function attackEntity(Entity $player){
 
  if($this->attackDelay > 10 && $this->distanceSquared($player) < 1){
  
   $this->attackDelay = 0;
   $ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getDamage());
   $player->attack($ev->getFinalDamage(), $ev);
  }
 }

 public function getDrops(){
 
  return [];
 }

}






















