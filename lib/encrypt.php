<?php
 goto c683ab452e6760fe5f8bb20f56609379; E0653b394ed4dcb0b20d7bf8b9619994: function passencryption($string) { goto Cf8ab027d08e56924942194a94532fa5; c4b259dd6818c6899eff6761ca0f993b: $encryption_key = "\114\x6b\65\x55\172\63\x73\x6c\170\x33\x42\x72\101\147\150\x53\61\141\x61\x57\65\101\x59\147\127\132\122\x56\x30\x74\111\130\x35\x65\x49\x30\171\120\143\150\x46\172\x34\75"; goto ac6157884e0afca8155c62b141ce9bb8; A353940482b294e80f5f5bbb957b3900: b468df0f3491d84a7857486530bb0de7: goto cabfdf637e901ab209e5decae3908a57; b410f20457bce17ddb6f682d21ae8ba9: if (!empty($string)) { goto b468df0f3491d84a7857486530bb0de7; } goto D767f6b7f35842a78c2890acd1bf4a51; bde5657fbd42f84c6bd0818ba0f82478: $encryption_iv = "\x31\62\63\x34\x35\66\67\x38\x39\x31\x30\x31\61\61\x32\61"; goto c4b259dd6818c6899eff6761ca0f993b; ac6157884e0afca8155c62b141ce9bb8: $encryption = openssl_encrypt($string, $ciphering, $encryption_key, $options, $encryption_iv); goto edf9ca6c185809ce8e288ec5b851e583; cabfdf637e901ab209e5decae3908a57: $ciphering = "\x41\105\123\x2d\61\x32\x38\55\103\102\x43"; goto d4fd45a4a2f7736d8e2409c4d6ccde14; a14f77bb66fe2c1eae608e939d7b825a: if (!($legacy == "\x59\145\163")) { goto A1c845c6f115cc5914499ee91bcaaaee; } goto e570a6b1e06ce1d5e9a0231389996657; edf9ca6c185809ce8e288ec5b851e583: return $encryption; goto ca1aa8fb5aadeec9120fb6eb7a7769a4; e570a6b1e06ce1d5e9a0231389996657: return $string; goto C70616d0d874cbeba19fd319984c2027; C70616d0d874cbeba19fd319984c2027: A1c845c6f115cc5914499ee91bcaaaee: goto b410f20457bce17ddb6f682d21ae8ba9; a2b9eac548cc587f627388fc6b149f3d: $options = 0; goto bde5657fbd42f84c6bd0818ba0f82478; d4fd45a4a2f7736d8e2409c4d6ccde14: $iv_length = openssl_cipher_iv_length($ciphering); goto a2b9eac548cc587f627388fc6b149f3d; Cf8ab027d08e56924942194a94532fa5: global $legacy; goto a14f77bb66fe2c1eae608e939d7b825a; D767f6b7f35842a78c2890acd1bf4a51: return $string; goto A353940482b294e80f5f5bbb957b3900; ca1aa8fb5aadeec9120fb6eb7a7769a4: } goto C493aba6f42df4b0a787ad349214674b; c683ab452e6760fe5f8bb20f56609379: $xmlversion = simplexml_load_file(DATA . "\166\145\x72\x73\151\157\156\x2e\x78\x6d\154"); goto A6ad2cb568608b5a3e8d749115bb8e92; A6ad2cb568608b5a3e8d749115bb8e92: $legacy = $xmlversion->legacy; goto E0653b394ed4dcb0b20d7bf8b9619994; C493aba6f42df4b0a787ad349214674b: function passdescryption($encryption) { goto Cd3ed4a0061f7fedf53da6afe4b31fb3; Cd3ed4a0061f7fedf53da6afe4b31fb3: global $legacy; goto d6cbbc0769c57d233011b1459042d6c9; c2e12102aa2de26661b4e1b182f194af: $options = 0; goto Cbb8c0fed6cc342c7f0fff3d8bcae0cd; f780893da37a489f8a292df1f2c312dd: $ciphering = "\x41\x45\123\x2d\x31\62\x38\55\103\102\x43"; goto b8592b66ac3417b0afed097afc692c17; c6cbb76d610d5df3000e4b2c30b5c36a: ce5f451d9b3fb8f71bf5a7fd6843346d: goto ee8d062b30a712f6d1c73499297d3b1d; b220c97f2dcfdbfbea2d186ee35f849f: return $encryption; goto c6cbb76d610d5df3000e4b2c30b5c36a; d6cbbc0769c57d233011b1459042d6c9: if (!($legacy == "\x59\145\163")) { goto ce5f451d9b3fb8f71bf5a7fd6843346d; } goto b220c97f2dcfdbfbea2d186ee35f849f; Bca9a7cdf68dafe3a27c9a785c97c2e6: $decryption_key = "\x4c\x6b\65\x55\x7a\63\163\x6c\x78\63\x42\x72\101\147\x68\x53\61\x61\x61\127\65\x41\x59\x67\127\132\x52\126\x30\x74\x49\x58\x35\x65\x49\x30\171\x50\143\150\106\x7a\x34\x3d"; goto c2e12102aa2de26661b4e1b182f194af; D1e49aef773e4b1ef3f0466ed8ca1202: return $encryption; goto b17f88d6062ff03375879ba78d9d8321; b8592b66ac3417b0afed097afc692c17: $decryption_iv = "\x31\x32\63\64\x35\66\x37\x38\x39\x31\60\x31\61\x31\62\61"; goto Bca9a7cdf68dafe3a27c9a785c97c2e6; Cbb8c0fed6cc342c7f0fff3d8bcae0cd: $decryption = openssl_decrypt($encryption, $ciphering, $decryption_key, $options, $decryption_iv); goto D70e9e71e7c7017fadc3b43a4cd0ab87; D70e9e71e7c7017fadc3b43a4cd0ab87: return $decryption; goto e574bc435544eabe57b297da0deef603; ee8d062b30a712f6d1c73499297d3b1d: if (!empty($encryption)) { goto F97c7cc3d7ca278cc94c7ac244535fe1; } goto D1e49aef773e4b1ef3f0466ed8ca1202; b17f88d6062ff03375879ba78d9d8321: F97c7cc3d7ca278cc94c7ac244535fe1: goto f780893da37a489f8a292df1f2c312dd; e574bc435544eabe57b297da0deef603: }
