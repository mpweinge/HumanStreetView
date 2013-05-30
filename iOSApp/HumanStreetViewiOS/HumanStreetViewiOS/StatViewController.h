//
//  StatViewController.h
//  HumanStreetViewiOS
//
//  Created by Michael Weingert on 2013-05-27.
//  Copyright (c) 2013 Michael Weingert. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface StatViewController : UIViewController
 <UIWebViewDelegate>
{
    int _isTakingPhoto;
    int _callbackID;
}
@property (weak, nonatomic) IBOutlet UIWebView *webView;



@end
