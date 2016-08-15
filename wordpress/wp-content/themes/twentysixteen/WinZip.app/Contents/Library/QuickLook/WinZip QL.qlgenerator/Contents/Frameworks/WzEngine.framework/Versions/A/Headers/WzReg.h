//
//  WzReg.h
//  WzEngine
//
//  Copyright 2010 Winzip. All rights reserved.
//

#import <Cocoa/Cocoa.h>


@interface WzReg : NSObject {

}

+ (BOOL)isActivationCode:(NSString *)code;
+ (BOOL)authenticationPassesForLicenseId:(NSString *)user licenseCode:(NSString *)code;
+ (NSData *)encryptedActivationDataForRequestString:(NSString *)request error:(NSError **)error;
+ (BOOL)activeWithMutliUserLicenseFile:(NSString *)mulFile MulRegData:(NSString *)MulRegData registerTo:(NSString **)to licenseCode:(NSString **)code;

@end
