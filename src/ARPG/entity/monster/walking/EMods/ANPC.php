<?php

namespace ARPG\entity\monster\walking\EMods;

use ARPG\entity\monster\WalkingMonster;
use pocketmine\Player;
use pocketmine\level\Level;
use pocketmine\utils\UUID;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\network\protocol\MobArmorEquipmentPacket;
use pocketmine\network\protocol\PlayerListPacket;

class ANPC extends WalkingMonster{

 const NETWORK_ID = 63;

 public $width = 0.6;
 public $height = 1.8;
 public $eyeHeight = 1.62;
 
 public function initEntity(){
  parent::initEntity();

  $this->setDamage([ 3, 5, 6, 7]);
 }
 
 public function getName(){
 
  return $this->getNameTag();
 }
 
 public function getUniqueId(){
  
  return UUID::fromData($this->getId(), $this->namedtag["Skin"]["Data"], $this->arpg["名字"]);
 }
   
 public function spawnTo(Player $player){

  if(isset($this->hasSpawned[$player->getLoaderId()]) or !isset($player->usedChunks[Level::chunkHash($this->chunk->getX(), $this->chunk->getZ())])) return;
   
  $this->server->updatePlayerListData($this->getUniqueId(), $this->getId(), $this->getName(), $this->namedtag["Skin"]["Name"], $this->namedtag["Skin"]["Data"]);
  
  $pk = new AddPlayerPacket();
  $pk->uuid = $this->getUniqueId();
  $pk->username = $this->getName();
  $pk->eid = $this->getId();
  $pk->x = $this->x;
  $pk->y = $this->y;
  $pk->z = $this->z;
  $pk->speedX = 0;
  $pk->speedY = 0;
  $pk->speedZ = 0;
  $pk->yaw = $this->yaw;
  $pk->pitch = $this->pitch;
  $pk->item = $this->addEnchant($this->namedtag["Held"]);;
  $pk->metadata = $this->dataProperties;
       
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
  
  $this->hasSpawned[$player->getLoaderId()] = $player;
 }
 
 public function kill(){
   
  parent::kill();
  
  $this->server->removePlayerListData($this->getUniqueId(), $this->level->getPlayers());
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
























