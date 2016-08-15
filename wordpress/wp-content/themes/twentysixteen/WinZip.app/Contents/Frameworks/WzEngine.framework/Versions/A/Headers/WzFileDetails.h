//
//  WzFileDetails.h
//  WzEngine
//
//  Copyright 2010 Winzip. All rights reserved.
//

#import <Cocoa/Cocoa.h>

@protocol WzFileDetails <NSObject>

@property (assign) NSString					*filename;
@property (assign) NSDate					*dateModified;
@property (assign) NSInteger				 uncompressedSize;
@property (assign) NSInteger				 compressedSize;
@property (assign) NSString					*comment;
@property (assign, readonly) BOOL			 encrypted;
@property (assign, readonly) BOOL            symlink;
@property (assign) WzEncryptionMethod		 encryptionMethod;

// from file attributes
@property (assign) BOOL						 isDirectory;

@end

id<WzFileDetails> WzEmptyFileDetails();
