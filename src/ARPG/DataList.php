<?php

namespace ARPG;

class DataList{
 
 public static $ModName = [
  "鸡" => "Chicken",
  "牛" => "Cow",
  "猪" => "Pig",
  "羊" => "Sheep",
  "狼" => "Wolf",
  "村民" => "Villager",
  "哞菇" => "Mooshroom",
  "鱿鱼" => "Squid",
  "兔子" => "Rabbit",
  "蝙蝠" => "Bat",
  "豹猫" => "Ocelot",
  "马" => "Horse",
  "驴" => "Donkey",
  "骡子" => "Mule",
  "骷髅马" => "SkeletonHorse",
  "僵尸马" => "ZombieHorse",
  "僵尸" => "Zombie",
  "苦力怕" => "Creeper",
  "骷髅" => "Skeleton",
  "蜘蛛" => "Spider",
  "僵尸猪人" => "PigZombie",
  "史莱姆" => "Slime",
  "末影人" => "Enderman",
  "蠢虫" => "Silverfish",
  "洞穴蜘蛛" => "CaveSpider",
  "恶魂" => "Ghast",
  "岩浆怪" => "LavaSlime",
  "烈焰人" => "Blaze",
  "女巫" => "Witch",
  "流浪者" => "Stray",
  "剥皮者" => "Husk",
  "凋零骷髅" => "WitherSkeleton",
  "雪傀儡" => "SnowGolem",
  "铁傀儡" => "IronGolem",
  "凋零" => "WitherBoss",
  "守卫者" => "Guardian",
  "老守卫者" => "elderguardian",
  "北极熊" => "PolarBear",
  "末影螨" => "Endermite",
  "潜匿之贝" => "Shulker",
  "末影龙" => "EnderDragon",
  "npc" => "NPC"
 ];
 
 public static $ModArmor = [
  [298, 299, 300, 301],
  [302, 303, 304, 305],
  [306, 307, 308, 309],
  [310, 311, 312, 313],
  [314, 315, 316, 317]
 ];
 
 public static $EMods = ["僵尸","骷髅","僵尸猪人","剥皮者","流浪者","凋零骷髅","npc"];
 
 public static $skin = "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";
}
































